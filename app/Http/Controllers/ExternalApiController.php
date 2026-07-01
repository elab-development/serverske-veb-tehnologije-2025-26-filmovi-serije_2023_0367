<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExternalApiController extends Controller
{
    // Funkcija koja povlači podatke sa OMDB-a na osnovu naslova filma
    public function searchOmdb(Request $request)
    {
        $title = $request->query('title');

        if (!$title) {
            return response()->json(['error' => 'Parametar title je obavezan'], 400);
        }

        $apiKey = config('services.omdb.key');
        
        // Slanje GET zahteva na OMDB API
        $response = Http::get("http://www.omdbapi.com/", [
            'apikey' => $apiKey,
            't' => $title
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Greska pri komunikaciji sa OMDB servisom'], 500);
        }

        return response()->json($response->json());
    }

    // Funkcija koja povlači trenutno popularne filmove sa TMDB-a
    public function getPopularTmdb()
    {
        $token = config('services.tmdb.token');

        // Slanje GET zahteva sa Bearer tokenom u zaglavlju
        $response = Http::withToken($token)
            ->get("https://api.themoviedb.org/3/movie/popular", [
                'language' => 'en-US',
                'page' => 1
            ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Greska pri komunikaciji sa TMDB servisom'], 500);
        }

        return response()->json($response->json());
    }
}
