<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/api_movies.php';
require __DIR__ . '/api_reviews.php';
require __DIR__ . '/api_external.php';

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
