<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Movie;
use App\Http\Requests\StoreReviewRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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

        $review = Review::create($validated);
        return response()->json(['success' => 'Recenzija uspesno kreirana.', 'data' => $review], 201);
    }

    // DELETE /api/reviews/{id}
    public function destroy($id): JsonResponse
    {
        $review = Review::find($id);
        if (!$review) {
            return response()->json(['error' => 'Recenzija ne postoji.'], 404);
        }

        if ($review->user_id !== Auth::id()) {
            return response()->json(['error' => 'Nemate autorizaciju za brisanje.'], 403);
        }

        $review->delete();
        return response()->json(['success' => 'Recenzija obrisana.'], 200);
    }
}

