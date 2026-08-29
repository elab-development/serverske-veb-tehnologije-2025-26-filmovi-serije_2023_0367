<?php

use App\Http\Controllers\MovieController;
use Illuminate\Support\Facades\Route;

Route::get('movies/stats/genres', [MovieController::class, 'genreStats']);
Route::get('movies/top-rated', [MovieController::class, 'topRated']);

Route::apiResource('movies', MovieController::class)->only(['index', 'show']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('movies', MovieController::class)->only(['store', 'update', 'destroy']);
});

Route::get('genres', [MovieController::class, 'indexGenres']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('genres', [MovieController::class, 'storeGenre']);
    Route::put('genres/{id}', [MovieController::class, 'updateGenre']);
    Route::delete('genres/{id}', [MovieController::class, 'destroyGenre']);
});