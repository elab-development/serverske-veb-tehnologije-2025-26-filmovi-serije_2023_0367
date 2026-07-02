# 🎬 Laravel Backend - Filmovi & Serije

Ovaj repozitorijum predstavlja jedinstveni Laravel backend projekat koji pokriva zahteve za **Domaći zadatak** i **Seminarski rad** iz predmeta *Serverske veb tehnologije*.

Projekat je razvijen kao REST API i služi za upravljanje filmovima, žanrovima, recenzijama i omiljenim sadržajem, uz integraciju sa eksternim servisima (TMDB i OMDB) i naprednim funkcionalnostima poput uvoza fajlova (poster) i kompleksnih DB operacija.

---

## 🚀 Tehnologije i Biblioteke

- **Framework**: Laravel 11 (PHP 8.2+)
- **Baza podataka**: MySQL (XAMPP)
- **Autentifikacija**: Laravel Sanctum
- **Klijent za testiranje**: Postman

---

## 🛠️ Instalacija i Pokretanje Lokalno

Pratite sledeće korake kako biste podesili projekat na svom računaru:

### 1. Kloniranje projekta i instalacija zavisnosti
```bash
# Klonirajte repozitorijum
git clone <url-repozitorijuma>
cd serverske-veb-tehnologije-2025-26-filmovi-serije

# Instalirajte PHP zavisnosti
composer install
```

### 2. Podešavanje baze i `.env` datoteke
1. Pokrenite **XAMPP Control Panel** i startujte **Apache** i **MySQL**.
2. Otvorite [phpMyAdmin](http://localhost/phpmyadmin) u browseru.
3. Kreirajte novu bazu podataka pod nazivom `filmovi_serije`.
4. Kopirajte `.env.example` u `.env`:
   ```bash
   cp .env.example .env
   ```
5. Otvorite `.env` i proverite/podesite sledeće varijable:
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

### 3. Migracije i Seeding (Demo podaci)
Pokrenite migracije kako biste kreirali sve tabele (uključujući novododate tipove migracija za izmenu kolona, spoljne ključeve i pivot tabele) i ubacili početne demo podatke (korisnike sa različitim ulogama, filmove, žanrove):
```bash
php artisan migrate --seed
```

### 4. Linkovanje Storage-a (za Upload Postera)
Kako bi slike koje korisnik upload-uje preko API-ja bile javno dostupne, kreirajte simbolički link:
```bash
php artisan storage:link
```

### 5. Pokretanje razvojnog servera
Pokrenite lokalni Laravel server:
```bash
php artisan serve
```
Server će raditi na adresi `http://127.0.0.1:8000`.

---

## 📬 Postman Kolekcija

Svi API endpointi su dokumentovani i mogu se direktno testirati.
- Kolekcija i globalne promenljive se nalaze u folderu `postman/`.
- Možete ih uvesti u svoj Postman (**Import** -> izaberite fajlove iz foldera `postman/collections/` i `postman/globals/`).

---

## 🔒 Korisničke Uloge (Role) i Kredencijali za Testiranje

Nakon pokretanja seeder-a, u bazi ćete imati sledeće korisnike:

| Uloga (Role) | Email | Lozinka | Opis |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@gmail.com` | `password` | Ima pristup CRUD operacijama nad filmovima (kreiranje, izmena, brisanje). |
| **Moderator** | `mod@gmail.com` | `password` | Pomoćna uloga u bazi podataka. |
| **Korisnik (User)** | `user@gmail.com` | `password` | Može pisati recenzije i dodavati filmove u omiljene (favorites). |
| **Gost (Guest)** | *Nema* | *Nema* | Može pretraživati filmove, gledati pojedinačne detalje i listati žanrove. |

---

## 📊 Pregled API Ruta

### 🔐 Autentifikacija
- `POST /api/register` - Registracija novog korisnika
- `POST /api/login` - Prijava i dobijanje Sanctum tokena
- `POST /api/logout` - Odjava (zahteva Sanctum token)

### 🎬 Filmovi (Movies)
- `GET /api/movies` - Prikaz filmova sa paginacijom, filterima (žanr, godina) i pretragom (`search`)
- `GET /api/movies/top-rated` - Top 5 najbolje ocenjenih filmova (eksplozivan 4-table SQL JOIN upit sa agregacijom)
- `GET /api/movies/{id}` - Prikaz detalja pojedinačnog filma
- `POST /api/movies` - Kreiranje novog filma (**samo Admin**)
- `PUT /api/movies/{id}` - Izmena filma (**samo Admin**)
- `DELETE /api/movies/{id}` - Brisanje filma (**samo Admin**)

### 📝 Recenzije i Favoriti (Reviews & Favorites)
- `GET /api/movies/{movie}/reviews` - Prikaz recenzija za određeni film (Ugnježdena ruta)
- `POST /api/reviews` - Dodavanje nove recenzije (Ulogovan korisnik, XSS zaštita)
- `DELETE /api/reviews/{id}` - Brisanje sopstvene recenzije
- `GET /api/users/{id}/favorites` - Prikaz omiljenih filmova korisnika (Ugnježdena ruta)
- `POST /api/favorites` - Dodavanje filma u omiljene
- `DELETE /api/favorites/{id}` - Uklanjanje filma iz omiljenih

### 🌐 Eksterni API-ji & Fajlovi
- `GET /api/external/omdb?title=Inception` - Pretraga filma na OMDB javnom API-ju
- `GET /api/external/tmdb/popular` - Popularni filmovi sa TMDB javnog API-ja
- `POST /api/movies/upload-poster` - Upload postera na lokalni server (vraća URL slike)
