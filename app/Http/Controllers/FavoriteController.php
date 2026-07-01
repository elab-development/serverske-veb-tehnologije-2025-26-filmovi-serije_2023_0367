<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    // 1. Dodavanje filma u omiljene (Watchlist)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'movie_id' => 'required|integer|exists:movies,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validacija neuspesna', 'poruke' => $validator->errors()], 400);
        }

        // Provera da li je korisnik vec dodao ovaj film u omiljene (da izbegnemo dupliranje)
        $postoji = Favorite::where('user_id', $request->user_id)
                           ->where('movie_id', $request->movie_id)
                           ->first();

        if ($postoji) {
            return response()->json(['message' => 'Film se vec nalazi u listi omiljenih'], 409); // 409 Conflict
        }

        $omiljeni = Favorite::create([
            'user_id' => $request->user_id,
            'movie_id' => $request->movie_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Film uspesno dodat u omiljene!',
            'podaci' => $omiljeni
        ], 201); // 201 Created
    }

    // 2. Brisanje filma iz omiljenih
    public function destroy($id)
    {
        $favorite = Favorite::find($id);

        if (!$favorite) {
            return response()->json(['error' => 'Zapis nije pronadjen'], 404); // 404 Not Found
        }

        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Film uspesno uklonjen iz liste omiljenih.'
        ], 200); // 200 OK
    }
}