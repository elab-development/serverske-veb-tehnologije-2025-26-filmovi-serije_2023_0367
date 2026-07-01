<?php

use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('movies/{movie}/reviews', [ReviewController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('reviews', [ReviewController::class, 'store']);
    Route::delete('reviews/{id}', [ReviewController::class, 'destroy']);
});