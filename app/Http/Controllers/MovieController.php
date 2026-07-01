<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Genre;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    /**
     * Prikaz svih filmova sa paginacijom, filtriranjem i pretragom (DAN 2)
     */
    public function index(Request $request)
    {
        $query = Movie::with('genre'); // Učitavamo vezu sa žanrom odjednom

        // 1. Pretraga po nazivu (Search)
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }

        // 2. Filtriranje po žanru (Filter)
        if ($request->has('genre_id')) {
            $query->where('genre_id', $request->input('genre_id'));
        }

        // 3. Filtriranje po godini
        if ($request->has('year')) {
            $query->where('year', $request->input('year'));
        }

        // 4. Sortiranje po nazivu (default je rastuće)
        $sortOrder = $request->input('sort', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy('title', $sortOrder);

        // 5. Paginacija (npr. 5 filmova po stranici)
        $movies = $query->paginate(5);

        return response()->json($movies, 200);
    }

    /**
     * Prikaz jednog filma
     */
    public function show($id)
    {
        $movie = Movie::with('genre')->find($id);

        if (!$movie) {
            return response()->json(['message' => 'Film nije pronađen.'], 404);
        }

        return response()->json($movie, 200);
    }

    /**
     * Kreiranje novog filma (Admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 5),
            'genre_id' => 'required|exists:genres,id',
            'poster_path' => 'nullable|string'
        ]);

        $movie = Movie::create($validated);

        return response()->json(['message' => 'Film uspešno kreiran.', 'data' => $movie], 201);
    }

    /**
     * Izmena filma (Admin)
     */
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
            'genre_id' => 'sometimes|required|exists:genres,id',
            'poster_path' => 'nullable|string'
        ]);

        $movie->update($validated);

        return response()->json(['message' => 'Film uspešno ažuriran.', 'data' => $movie], 200);
    }

    /**
     * Brisanje filma (Admin)
     */
    public function destroy($id)
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json(['message' => 'Film nije pronađen.'], 404);
        }

        $movie->delete();

        return response()->json(['message' => 'Film uspešno obrisan.'], 200);
    }

    /**
     * Prikaz svih žanrova
     */
    public function indexGenres()
    {
        $genres = Genre::all();
        return response()->json($genres, 200);
    }
}
