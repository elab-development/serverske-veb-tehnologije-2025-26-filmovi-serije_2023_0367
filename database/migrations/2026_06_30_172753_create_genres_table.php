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
    Schema::create('genres', function (Blueprint $table) {
        $table->id(); // Tip 1: Kreiranje primarnog ključa (Inicijalna migracija)
        $table->string('name')->unique(); // Dodajemo naziv žanra koji mora biti unikatan
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('genres');
    }
};
