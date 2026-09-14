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
        Schema::table('actor_movie', function (Blueprint $table) {
            $table->foreign('actor_id')->references('id')->on('actors')->onDelete('cascade');
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actor_movie', function (Blueprint $table) {
            $table->dropForeign(['actor_id']);
            $table->dropForeign(['movie_id']);
        });
    }
};
