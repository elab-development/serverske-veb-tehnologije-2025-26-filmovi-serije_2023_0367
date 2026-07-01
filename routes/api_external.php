<?php

use App\Http\Controllers\ExternalApiController;
use App\Http\Controllers\MoviePosterController;
use App\Http\Controllers\FavoriteController;
use Illuminate\Support\Facades\Route;

Route::get('/external/omdb', [ExternalApiController::class, 'searchOmdb']);
Route::get('/external/tmdb/popular', [ExternalApiController::class, 'getPopularTmdb']);
Route::post('/movies/upload-poster', [MoviePosterController::class, 'uploadPoster']);
Route::post('/favorites', [FavoriteController::class, 'store']);
Route::delete('/favorites/{id}', [FavoriteController::class, 'destroy']);