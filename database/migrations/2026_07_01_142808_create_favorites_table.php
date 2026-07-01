<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            // Strani kljuc koji povezuje omiljeni film sa tabelom users
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Strani kljuc koji povezuje tabelu sa tabelom movies
            $table->foreignId('movie_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
