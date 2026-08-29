<?php

use App\Http\Controllers\MovieController;
use Illuminate\Support\Facades\Route;

Route::get('movies/stats/genres', [MovieController::class, 'genreStats']);
Route::get('movies/top-rated', [MovieController::class, 'topRated']);
Route::get('genres', [MovieController::class, 'indexGenres']);

Route::apiResource('movies', MovieController::class)->only(['index', 'show']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('movies', [MovieController::class, 'store']);
    Route::put('movies/{id}', [MovieController::class, 'update']);
    Route::delete('movies/{id}', [MovieController::class, 'destroy']);

    Route::post('genres', [MovieController::class, 'storeGenre']);
    Route::put('genres/{id}', [MovieController::class, 'updateGenre']);
    Route::delete('genres/{id}', [MovieController::class, 'destroyGenre']);
});