<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Movie;
use App\Http\Requests\StoreReviewRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ReviewController extends Controller
{
    // GET /api/movies/{id}/reviews (Ugnježdena ruta)
    public function index($movieId): JsonResponse
    {
        $movie = Movie::find($movieId);
        if (!$movie) {
            return response()->json(['error' => 'Film nije pronadjen.'], 404);
        }

        $reviews = Review::where('movie_id', $movieId)->with('user:id,name')->get();
        return response()->json(['success' => true, 'data' => $reviews], 200);
    }

    // POST /api/reviews (Zaštita autentičnosti)
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['text'] = strip_tags($validated['text']); // XSS Zaštita
        $validated['user_id'] = Auth::id();

        // Transakcija: kreiranje recenzije + rekalkulacija prosecne ocene filma
        // moraju uspeti zajedno, u suprotnom se sve ponistava (ROLLBACK)
        $review = DB::transaction(function () use ($validated) {
            $review = Review::create($validated);
            $this->recalculateAvgRating($validated['movie_id']);
            return $review;
        });

        return response()->json(['success' => 'Recenzija uspesno kreirana.', 'data' => $review], 201);
    }

    // DELETE /api/reviews/{id}
    public function destroy($id): JsonResponse
    {
        $review = Review::find($id);
        if (!$review) {
            return response()->json(['error' => 'Recenzija ne postoji.'], 404);
        }
 
        $user = Auth::user();
        $isOwner = $review->user_id === $user->id;
        $canModerate = in_array($user->role, ['moderator', 'admin']);
 
        // Autor sme da obrise svoju recenziju
        // Moderator i admin smeju da obrisu BILO CIJU recenziju
        if (!$isOwner && !$canModerate) {
            return response()->json(['error' => 'Nemate autorizaciju za brisanje.'], 403);
        }
 
        $movieId = $review->movie_id;
 
        DB::transaction(function () use ($review, $movieId) {
            $review->delete();
            $this->recalculateAvgRating($movieId);
        });
 
        return response()->json(['success' => 'Recenzija obrisana.'], 200);
    }

    // GET /api/reviews/{id}
    public function show($id): JsonResponse
    {
        $review = Review::with('user:id,name', 'movie:id,title')->find($id);

        if (!$review) {
            return response()->json(['error' => 'Recenzija nije pronađena.'], 404);
        }

        return response()->json(['success' => true, 'data' => $review], 200);
    }

    // PUT/PATCH /api/reviews/{id}
    public function update(\Illuminate\Http\Request $request, $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['error' => 'Recenzija ne postoji.'], 404);
        }

        if ($review->user_id !== Auth::id()) {
            return response()->json(['error' => 'Nemate autorizaciju za izmenu ove recenzije.'], 403);
        }

        $validated = $request->validate([
            'text' => 'sometimes|required|string|min:5|max:1000',
            'rating' => 'sometimes|required|integer|min:1|max:5',
        ]);

        if (isset($validated['text'])) {
            $validated['text'] = strip_tags($validated['text']); // XSS Zaštita
        }

        DB::transaction(function () use ($review, $validated) {
            $review->update($validated);
            $this->recalculateAvgRating($review->movie_id);
        });

        return response()->json(['success' => 'Recenzija uspešno izmenjena.', 'data' => $review->fresh()], 200);
    }

    public function userFavorites($userId): JsonResponse
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'Korisnik nije pronađen.'], 404);
        }

        $favorites = $user->favorites()->get();

        return response()->json([
            'success' => true,
            'user' => $user->name,
            'data' => $favorites
        ], 200);
    }

    /**
     * Rekalkulise i cuva prosecnu ocenu (avg_rating) filma na osnovu
     * svih njegovih recenzija. Poziva se unutar DB transakcije.
     */
    private function recalculateAvgRating(int $movieId): void
    {
        $avg = Review::where('movie_id', $movieId)->avg('rating');

        Movie::where('id', $movieId)->update([
            'avg_rating' => $avg ? round($avg, 1) : null,
        ]);
    }
}