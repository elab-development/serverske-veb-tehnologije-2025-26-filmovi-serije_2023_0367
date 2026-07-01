<?php

use App\Http\Controllers\ExternalApiController;
use Illuminate\Support\Facades\Route;

// Ruta za pretragu filmova preko OMDB servisa
Route::get('/external/omdb', [ExternalApiController::class, 'searchOmdb']);

// Ruta za dobijanje popularnih filmova preko TMDB servisa
Route::get('/external/tmdb/popular', [ExternalApiController::class, 'getPopularTmdb']);