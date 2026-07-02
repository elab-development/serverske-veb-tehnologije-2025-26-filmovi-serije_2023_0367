<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = ['title', 'description', 'year', 'poster_path', 'genre_id'];

    public function genre() {
        return $this->belongsTo(Genre::class);
    }

    public function reviews() {
        return $this->hasMany(Review::class);
    }
}
