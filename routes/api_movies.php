<?php

use App\Http\Controllers\MovieController;
use Illuminate\Support\Facades\Route;

// Rute dostupne svima (Gosti i ulogovani)
Route::get('movies', [MovieController::class, 'index']);
Route::get('movies/{id}', [MovieController::class, 'show']);

// Rute za žanrove (Možeš ih staviti u isti kontroler radi jednostavnosti)
Route::get('genres', [MovieController::class, 'indexGenres']);

// Zaštićene rute - Samo AUTENTIFIKOVANI ADMINI mogu da menjaju podatke
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('movies', [MovieController::class, 'store']);
    Route::put('movies/{id}', [MovieController::class, 'update']);
    Route::delete('movies/{id}', [MovieController::class, 'destroy']);
});
