<?php

use App\Http\Controllers\MovieController;
use Illuminate\Support\Facades\Route;

// Custom rute van resource kontrolera
Route::get('movies/top-rated', [MovieController::class, 'topRated']);
Route::get('genres', [MovieController::class, 'indexGenres']);

// Resource ruta za javne akcije (indeks i prikaz jednog filma)
Route::apiResource('movies', MovieController::class)->only(['index', 'show']);

// Zaštićene resource rute za administrativne akcije
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('movies', MovieController::class)->except(['index', 'show']);
});
