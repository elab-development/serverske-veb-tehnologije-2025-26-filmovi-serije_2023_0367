<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovieController extends Controller
{
    /**
     * Prikaz svih filmova sa paginacijom, filtriranjem i pretragom
     */
    public function index(Request $request)
    {
        $query = Movie::with('genre');

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->has('genre_id')) {
            $query->where('genre_id', $request->input('genre_id'));
        }

        if ($request->has('year')) {
            $query->where('year', $request->input('year'));
        }

        $sortOrder = $request->input('sort', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy('title', $sortOrder);

        $movies = $query->paginate(5);

        return response()->json($movies, 200);
    }

    public function show($id)
    {
        $movie = Movie::with('genre')->find($id);

        if (!$movie) {
            return response()->json(['message' => 'Film nije pronađen.'], 404);
        }

        return response()->json($movie, 200);
    }

    public function topRated()
    {
        $topMovies = Movie::with('genre')
            ->withAvg('reviews', 'rating')
            ->orderBy('reviews_avg_rating', 'desc')
            ->take(5)
            ->get();

        return response()->json($topMovies, 200);
    }

    /**
     * GET /api/movies/stats/genres
     * Napredna manipulacija podacima: eksplicitni JOIN + agregacija + grupisanje.
     * Za svaki zanr racuna broj filmova i prosecnu ocenu svih recenzija tih filmova.
     */
    public function genreStats()
    {
        $stats = DB::table('genres')
            ->leftJoin('movies', 'movies.genre_id', '=', 'genres.id')
            ->leftJoin('reviews', 'reviews.movie_id', '=', 'movies.id')
            ->select(
                'genres.id as genre_id',
                'genres.name as genre',
                DB::raw('COUNT(DISTINCT movies.id) as movie_count'),
                DB::raw('ROUND(AVG(reviews.rating), 2) as avg_rating')
            )
            ->groupBy('genres.id', 'genres.name')
            ->orderByDesc('movie_count')
            ->get();

        return response()->json($stats, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 5),
            'release_date' => 'nullable|date',
            'genre_id' => 'required|exists:genres,id',
            'poster_path' => 'nullable|string'
        ]);

        $movie = Movie::create($validated);

        return response()->json(['message' => 'Film uspešno kreiran.', 'data' => $movie], 201);
    }

    public function update(Request $request, $id)
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json(['message' => 'Film nije pronađen.'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'year' => 'sometimes|required|integer|min:1900',
            'release_date' => 'nullable|date',
            'genre_id' => 'sometimes|required|exists:genres,id',
            'poster_path' => 'nullable|string'
        ]);

        $movie->update($validated);

        return response()->json(['message' => 'Film uspešno ažuriran.', 'data' => $movie], 200);
    }

    public function destroy($id)
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json(['message' => 'Film nije pronađen.'], 404);
        }

        $movie->delete();

        return response()->json(['message' => 'Film uspešno obrisan.'], 200);
    }

    public function indexGenres()
    {
        $genres = Genre::all();
        return response()->json($genres, 200);
    }

    /**
     * POST /api/genres (samo admin)
     */
    public function storeGenre(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:genres,name',
        ]);

        $genre = Genre::create($validated);

        return response()->json(['message' => 'Žanr uspešno kreiran.', 'data' => $genre], 201);
    }

    public function updateGenre(Request $request, $id)
    {
        $genre = Genre::find($id);

        if (!$genre) {
            return response()->json(['message' => 'Žanr nije pronađen.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:genres,name,' . $genre->id,
        ]);

        $genre->update($validated);

        return response()->json(['message' => 'Žanr uspešno ažuriran.', 'data' => $genre], 200);
    }

    public function destroyGenre($id)
    {
        $genre = Genre::find($id);

        if (!$genre) {
            return response()->json(['message' => 'Žanr nije pronađen.'], 404);
        }

        $genre->delete();

        return response()->json(['message' => 'Žanr uspešno obrisan.'], 200);
    }
}