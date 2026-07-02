<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Unos 3 korisnika sa različitim ulogama (Ispunjavanje uslova za 3 role)
        DB::table('users')->insertOrIgnore([
            [
                'id' => 1,
                'name' => 'Admin Korisnik',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Moderator Korisnik',
                'email' => 'mod@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'moderator',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Standardan Korisnik',
                'email' => 'user@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 2. Unos bazičnih žanrova
        DB::table('genres')->insertOrIgnore([
            ['id' => 1, 'name' => 'Akcija', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Komedija', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Sci-Fi', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Unos lažnih filmova sa mapiranim genre_id (Ispunjavanje uslova za movies)
        DB::table('movies')->insertOrIgnore([
            [
                'id' => 1,
                'title' => 'Inception',
                'description' => 'A thief who steals corporate secrets through the use of dream-sharing technology.',
                'year' => 2010,
                'poster_path' => 'posters/placeholder.jpg',
                'genre_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'title' => 'The Dark Knight',
                'description' => 'When the menace known as the Joker wreaks havoc and chaos on the people of Gotham.',
                'year' => 2008,
                'poster_path' => 'posters/placeholder.jpg',
                'genre_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'title' => 'The Hangover',
                'description' => 'Three buddies wake up from a bachelor party in Las Vegas, with no memory of the previous night.',
                'year' => 2009,
                'poster_path' => 'posters/placeholder.jpg',
                'genre_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 4. Unos testnih zapisa u omiljene (Tvoj deo posla - favorites pivot tabela)
        DB::table('favorites')->insertOrIgnore([
            ['user_id' => 1, 'movie_id' => 1],
            ['user_id' => 1, 'movie_id' => 2],
            ['user_id' => 3, 'movie_id' => 1],
        ]);
    }
}