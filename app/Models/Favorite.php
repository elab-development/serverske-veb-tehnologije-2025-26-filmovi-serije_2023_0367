<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    // Dozvoljavamo masovno dodavanje za kljucna polja (zahtev za stabilnost)
    protected $fillable = ['user_id', 'movie_id'];

    // Relacija: Svaki zapis u tabeli favorites pripada jednom korisniku
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relacija: Svaki zapis u tabeli favorites pripada jednom filmu
    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
}