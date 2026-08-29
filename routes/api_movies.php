<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\MoviePosterController;
use Illuminate\Support\Facades\Route;

Route::get('movies/stats/genres', [MovieController::class, 'genreStats']);
Route::get('movies/top-rated', [MovieController::class, 'topRated']);
Route::get('genres', [MovieController::class, 'indexGenres']);

Route::get('movies', [MovieController::class, 'index']);
Route::get('movies/{id}', [MovieController::class, 'show']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('movies', [MovieController::class, 'store']);
    Route::put('movies/{id}', [MovieController::class, 'update']);
    Route::delete('movies/{id}', [MovieController::class, 'destroy']);
    Route::post('movies/upload-poster', [MoviePosterController::class, 'uploadPoster']);

    Route::post('genres', [MovieController::class, 'storeGenre']);
    Route::put('genres/{id}', [MovieController::class, 'updateGenre']);
    Route::delete('genres/{id}', [MovieController::class, 'destroyGenre']);
});