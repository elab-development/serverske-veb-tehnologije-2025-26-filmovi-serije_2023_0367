<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Movie;
use App\Models\Genre;
use App\Models\Review;
use App\Models\Favorite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SpecificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. BAZA I MODELI
     */
    public function test_models_and_database_structure_exists()
    {
        // Provera da li tabele postoje u bazi
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('movies'));
        $this->assertTrue(Schema::hasTable('genres'));
        $this->assertTrue(Schema::hasTable('reviews'));
        $this->assertTrue(Schema::hasTable('favorites'));

        // Provera kolone role sa podrazumevanom vrednoscu 'user'
        $this->assertTrue(Schema::hasColumn('users', 'role'));
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $user->refresh();
        $this->assertEquals('user', $user->role);
    }

    public function test_model_relationships()
    {
        // Kreiranje zvanicnih test podataka
        $genre = Genre::create(['name' => 'Drama']);
        
        $user = User::create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => bcrypt('password123'),
        ]);

        $movie = Movie::create([
            'title' => 'The Shawshank Redemption',
            'description' => 'Two imprisoned men bond over a number of years.',
            'year' => 1994,
            'genre_id' => $genre->id,
            'poster_path' => 'shawshank.jpg'
        ]);

        $review = Review::create([
            'text' => 'Amazing movie!',
            'rating' => 5,
            'user_id' => $user->id,
            'movie_id' => $movie->id,
        ]);

        // Dodavanje u favorites
        $user->favorites()->attach($movie->id);

        // 1. User -> reviews (hasMany) i belongsToMany(Movie) preko favorites
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->reviews);
        $this->assertTrue($user->reviews->contains($review));
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->favorites);
        $this->assertTrue($user->favorites->contains($movie));

        // 2. Movie -> belongsTo(Genre) i hasMany(Review)
        $this->assertInstanceOf(Genre::class, $movie->genre);
        $this->assertEquals($genre->id, $movie->genre->id);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $movie->reviews);
        $this->assertTrue($movie->reviews->contains($review));

        // 3. Genre -> movies (hasMany)
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $genre->movies);
        $this->assertTrue($genre->movies->contains($movie));

        // 4. Review -> user (belongsTo) i movie (belongsTo)
        $this->assertInstanceOf(User::class, $review->user);
        $this->assertEquals($user->id, $review->user->id);
        
        $this->assertInstanceOf(Movie::class, $review->movie);
        $this->assertEquals($movie->id, $review->movie->id);

        // 5. Favorite model relacije
        $favoriteRecord = Favorite::first();
        $this->assertNotNull($favoriteRecord);
        $this->assertInstanceOf(User::class, $favoriteRecord->user);
        $this->assertInstanceOf(Movie::class, $favoriteRecord->movie);
    }

    /**
     * 2. AUTENTIFIKACIJA
     */
    public function test_user_authentication_flow()
    {
        // Registracija (koristi AuthController sa confirmed password)
        $registerData = [
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/register', $registerData);
        $response->assertStatus(201);
        $response->assertJsonStructure(['user', 'token']);
        
        $this->assertDatabaseHas('users', ['email' => 'bob@example.com']);

        // Login
        $loginData = [
            'email' => 'bob@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/login', $loginData);
        $response->assertStatus(200);
        $response->assertJsonStructure(['user', 'token']);
        
        $token = $response->json('token');

        // Logout
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');
        
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Uspešno ste se odjavili']);
    }

    public function test_guest_cannot_create_movie()
    {
        $genre = Genre::create(['name' => 'Sci-Fi']);
        $movieData = [
            'title' => 'Inception',
            'description' => 'A thief who steals corporate secrets...',
            'year' => 2010,
            'genre_id' => $genre->id,
        ];
        $this->postJson('/api/movies', $movieData)->assertStatus(401);
    }

    public function test_regular_user_cannot_create_movie()
    {
        $genre = Genre::create(['name' => 'Sci-Fi']);
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);

        $movieData = [
            'title' => 'Inception',
            'description' => 'A thief who steals corporate secrets...',
            'year' => 2010,
            'genre_id' => $genre->id,
        ];

        $userToken = $regularUser->createToken('test_token')->plainTextToken;
        $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->postJson('/api/movies', $movieData)
            ->assertStatus(403)
            ->assertJson(['message' => 'Nemate autorizaciju za ovu akciju.']);
    }

    public function test_admin_can_create_movie()
    {
        $genre = Genre::create(['name' => 'Sci-Fi']);
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);
        $adminUser->role = 'admin';
        $adminUser->save();

        $movieData = [
            'title' => 'Inception',
            'description' => 'A thief who steals corporate secrets...',
            'year' => 2010,
            'genre_id' => $genre->id,
        ];

        $adminToken = $adminUser->createToken('test_token')->plainTextToken;
        $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->postJson('/api/movies', $movieData)
            ->assertStatus(201);
    }

    /**
     * 3. MOVIE CRUD, PAGINACIJA I FILTERI
     */
    public function test_movies_index_pagination_and_filtering()
    {
        $genre1 = Genre::create(['name' => 'Action']);
        $genre2 = Genre::create(['name' => 'Comedy']);

        // Kreiranje više od 5 filmova da bismo testirali paginaciju
        Movie::create(['title' => 'Batman Begins', 'year' => 2005, 'genre_id' => $genre1->id]);
        Movie::create(['title' => 'Casino Royale', 'year' => 2006, 'genre_id' => $genre1->id]);
        Movie::create(['title' => 'Superbad', 'year' => 2007, 'genre_id' => $genre2->id]);
        Movie::create(['title' => 'The Hangover', 'year' => 2009, 'genre_id' => $genre2->id]);
        Movie::create(['title' => 'Toy Story', 'year' => 1995, 'genre_id' => $genre2->id]);
        Movie::create(['title' => 'Zombieland', 'year' => 2009, 'genre_id' => $genre2->id]);

        // 1. GET /api/movies – Vraća listu sa paginacijom (5 filmova po stranici)
        $response = $this->getJson('/api/movies');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => ['id', 'title', 'year', 'genre_id', 'genre']
            ],
            'total',
            'per_page'
        ]);
        
        $this->assertCount(5, $response->json('data'));
        $this->assertEquals(6, $response->json('total'));
        $this->assertEquals(5, $response->json('per_page'));

        // 2. Pretraga po nazivu (?search=Batman)
        $response = $this->getJson('/api/movies?search=Batman');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Batman Begins', $response->json('data.0.title'));

        // 3. Filter po žanru (?genre_id)
        $response = $this->getJson('/api/movies?genre_id=' . $genre1->id);
        $response->assertStatus(200);
        // Batman i Casino Royale su u genre1
        $this->assertCount(2, $response->json('data'));

        // 4. Sortiranje po nazivu desc (?sort=desc)
        // Ocekujemo abecedni redosled unazad: Zombieland, Toy Story, The Hangover, Superbad, Casino Royale (prvih 5)
        $response = $this->getJson('/api/movies?sort=desc');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Zombieland', $data[0]['title']);
        $this->assertEquals('Toy Story', $data[1]['title']);
        $this->assertEquals('The Hangover', $data[2]['title']);
        $this->assertEquals('Superbad', $data[3]['title']);
        $this->assertEquals('Casino Royale', $data[4]['title']);

        // Sortiranje po nazivu asc (?sort=asc ili default)
        $response = $this->getJson('/api/movies?sort=asc');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Batman Begins', $data[0]['title']);
    }

    public function test_movie_show_details_with_genre()
    {
        $genre = Genre::create(['name' => 'Sci-Fi']);
        $movie = Movie::create([
            'title' => 'Interstellar',
            'year' => 2014,
            'genre_id' => $genre->id
        ]);

        $response = $this->getJson('/api/movies/' . $movie->id);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id', 'title', 'year', 'genre_id', 'genre' => ['id', 'name']
        ]);
        $this->assertEquals('Sci-Fi', $response->json('genre.name'));

        // Nepostojeći film vraća 404
        $this->getJson('/api/movies/99999')->assertStatus(404);
    }

    public function test_admin_movie_crud_operations()
    {
        $genre1 = Genre::create(['name' => 'Action']);
        $genre2 = Genre::create(['name' => 'Drama']);
        
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin_crud@example.com',
            'password' => bcrypt('password123'),
        ]);
        $admin->role = 'admin';
        $admin->save();

        $token = $admin->createToken('admin_token')->plainTextToken;

        // 1. Create (POST)
        $movieData = [
            'title' => 'Gladiator',
            'year' => 2000,
            'genre_id' => $genre1->id,
            'description' => 'A former Roman General sets out to exact vengeance...'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/movies', $movieData);
        
        $response->assertStatus(201);
        $movieId = $response->json('data.id');

        // 2. Update (PUT)
        $updateData = [
            'title' => 'Gladiator Remastered',
            'genre_id' => $genre2->id
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/movies/' . $movieId, $updateData);
        
        $response->assertStatus(200);
        $this->assertEquals('Gladiator Remastered', $response->json('data.title'));
        $this->assertEquals($genre2->id, $response->json('data.genre_id'));

        // 3. Delete (DELETE)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/movies/' . $movieId);
        
        $response->assertStatus(200);
        $this->assertDatabaseMissing('movies', ['id' => $movieId]);
    }
}
