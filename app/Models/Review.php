<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['text', 'rating', 'user_id', 'movie_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function movie() {
        return $this->belongsTo(Movie::class);
    }
}
