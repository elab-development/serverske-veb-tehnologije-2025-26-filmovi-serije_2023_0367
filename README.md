# Filmovi i Serije - Laravel Backend REST API

Projekat je razvijen kao REST API i služi za upravljanje filmovima, žanrovima, recenzijama i omiljenim
sadržajem, uz integraciju sa eksternim javnim servisima (TMDB i OMDB) i naprednim funkcionalnostima poput
upload-a fajlova (posteri), paginacije, filtriranja, pretrage, DB transakcija i eksplicitnih SQL JOIN upita
sa agregacijom.

---

## Tehnologije i biblioteke

- Framework: Laravel 13 (PHP 8.2+)
- Baza podataka: MySQL (XAMPP)
- Autentifikacija: Laravel Sanctum (token-based)
- Klijent za testiranje: Postman
- Javni servisi: OMDb API, TMDB API

---

## Instalacija i pokretanje lokalno

### 1. Kloniranje projekta i instalacija zavisnosti

```bash
git clone https://github.com/elab-development/serverske-veb-tehnologije-2025-26-filmovi-serije_2023_0367.git
cd serverske-veb-tehnologije-2025-26-filmovi-serije_2023_0367

composer install

# Potrebno za migracije koje menjaju tip postojece kolone (change())
composer require doctrine/dbal
```

### 2. Podešavanje baze i .env datoteke

1. Pokrenite XAMPP Control Panel i startujte Apache i MySQL.
2. Otvorite phpMyAdmin (http://localhost/phpmyadmin) u browseru.
3. Kreirajte novu bazu podataka (npr. `filmovi_serije`).
4. Kopirajte `.env.example` u `.env`:
   ```bash
   cp .env.example .env
   ```
5. Otvorite `.env` i podesite:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=filmovi_serije
   DB_USERNAME=root
   DB_PASSWORD=
   ```
6. Generišite ključ aplikacije:
   ```bash
   php artisan key:generate
   ```

### 3. Konfiguracija javnih API servisa

U `.env` dodajte:
```env
OMDB_API_KEY=tvoj_omdb_kljuc
TMDB_API_TOKEN=tvoj_tmdb_bearer_token
```

i proverite u `config/services.php`:
```php
'omdb' => [
    'key' => env('OMDB_API_KEY'),
],

'tmdb' => [
    'token' => env('TMDB_API_TOKEN'),
],
```

### 4. Migracije i seed podaci

Pokrenite migracije kako biste kreirali sve tabele (uključujući migracije koje menjaju kolone, dodaju
ograničenja i spoljne ključeve) i ubacili početne demo podatke:
```bash
php artisan migrate --seed
```

### 5. Linkovanje storage-a (za upload postera)

```bash
php artisan storage:link
```

### 6. Pokretanje razvojnog servera

```bash
php artisan serve
```
Server radi na adresi `http://127.0.0.1:8000`, a API rute na `http://127.0.0.1:8000/api/...`.

---

## Postman kolekcija

Svi API endpointi su dokumentovani i mogu se direktno testirati.
- Kolekcija se nalazi u folderu `postman/`.
- Uvezite je u Postman preko Import -> izaberite fajl iz `postman/`.

---

## Korisničke uloge

| Uloga | Opis |
| :--- | :--- |
| Gost (neulogovan) | Pretraga filmova, pregled detalja, žanrova, recenzija i statistike |
| Autentifikovani korisnik (user) | Sve što i gost, plus pisanje/izmena/brisanje SVOJIH recenzija i upravljanje sopstvenim omiljenim filmovima |
| Moderator | Sve što i user, plus brisanje BILO ČIJE recenzije (moderacija neprikladnog sadržaja) - nema pristup upravljanju filmovima/žanrovima |
| Administrator | Sve gore navedeno, plus pun CRUD nad filmovima i žanrovima |

Testni nalozi (kreirani kroz seeder):

| Uloga | Email | Lozinka |
| :--- | :--- | :--- |
| Admin | admin@gmail.com | password |
| Moderator | mod@gmail.com | password |
| User | user@gmail.com | password |

---

## Pregled API ruta

### Autentifikacija
- `POST /api/register` - registracija novog korisnika
- `POST /api/login` - prijava i dobijanje Sanctum tokena
- `POST /api/logout` - odjava (zahteva Sanctum token)
- `GET /api/user` - podaci o prijavljenom korisniku (zahteva token)

### Filmovi (Movies)
- `GET /api/movies` - lista filmova sa paginacijom, filtriranjem (`genre_id`, `year`) i pretragom (`search`)
- `GET /api/movies/top-rated` - top 5 najbolje ocenjenih filmova
- `GET /api/movies/stats/genres` - statistika po žanru: broj filmova i prosečna ocena (eksplicitni SQL JOIN + agregacija + grupisanje)
- `GET /api/movies/{id}` - detalji filma
- `POST /api/movies` - kreiranje filma (samo admin)
- `PUT /api/movies/{id}` - izmena filma (samo admin)
- `DELETE /api/movies/{id}` - brisanje filma (samo admin)

### Žanrovi (Genres)
- `GET /api/genres` - lista žanrova
- `POST /api/genres` - kreiranje žanra (samo admin)
- `PUT /api/genres/{id}` - izmena žanra (samo admin)
- `DELETE /api/genres/{id}` - brisanje žanra (samo admin)

### Recenzije i favoriti (Reviews & Favorites)
- `GET /api/movies/{movie}/reviews` - recenzije za dati film (ugnježdena ruta)
- `POST /api/reviews` - dodavanje recenzije (autentifikovan korisnik, XSS zaštita, DB transakcija koja ažurira prosečnu ocenu filma)
- `GET /api/reviews/{id}` - detalji recenzije
- `PUT /api/reviews/{id}` - izmena SVOJE recenzije (samo autor)
- `DELETE /api/reviews/{id}` - brisanje recenzije (autor, ili moderator/admin radi moderacije)
- `GET /api/users/{id}/favorites` - omiljeni filmovi korisnika (ugnježdena ruta)
- `POST /api/favorites` - dodavanje filma u omiljene
- `DELETE /api/favorites/{id}` - uklanjanje filma iz omiljenih

### Eksterni API-ji i fajlovi
- `GET /api/external/omdb?title=Inception` - pretraga filma preko OMDb javnog servisa
- `GET /api/external/tmdb/popular` - popularni filmovi sa TMDB javnog servisa
- `POST /api/movies/upload-poster` - upload postera filma (vraća javni URL slike)

---

## Struktura projekta

```
app/
  Http/
    Controllers/     -> AuthController, MovieController, ReviewController,
                         FavoriteController, MoviePosterController, ExternalApiController
  Models/             -> User, Movie, Genre, Review, Favorite
database/
  migrations/         -> definicije i naknadne izmene struktura tabela
  seeders/             -> DatabaseSeeder (test korisnici, žanrovi, filmovi, favoriti)
routes/
  api.php             -> ulazna tačka za API rute
  api_movies.php       -> rute za filmove i žanrove
  api_reviews.php      -> rute za recenzije i omiljene
  api_external.php     -> rute za spoljne servise i upload
postman/                -> Postman kolekcija i primeri testiranja
```

