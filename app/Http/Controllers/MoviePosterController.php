<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MoviePosterController extends Controller
{
    public function uploadPoster(Request $request)
    {
        // 1. Validacija zahteva
        $validator = Validator::make($request->all(), [
            'poster' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // slika do 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validacija neuspesna',
                'poruke' => $validator->errors()
            ], 400); // 400 Bad Request
        }

        // 2. Obrada i cuvanje fajla
        if ($request->hasFile('poster')) {
            $file = $request->file('poster');
            
            // Generisanje jedinstvenog imena (timestamp + originalni naziv)
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Cuvanje na 'public' disk unutar foldera 'posters'
            $path = $file->storeAs('posters', $fileName, 'public');

            // Pravljenje apsolutnog URL-a za frontend
            $url = asset('storage/' . $path);

            return response()->json([
                'success' => true,
                'message' => 'Poster uspesno sacuvan na serveru!',
                'poster_url' => $url
            ], 201); // 201 Created
        }

        return response()->json(['error' => 'Fajl nije prosledjen'], 400);
    }
}