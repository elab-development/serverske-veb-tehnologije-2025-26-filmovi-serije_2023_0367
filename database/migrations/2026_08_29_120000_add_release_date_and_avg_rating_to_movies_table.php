<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->date('release_date')->nullable()->after('year');
            $table->decimal('avg_rating', 3, 1)->nullable()->after('poster_path');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn(['release_date', 'avg_rating']);
        });
    }
};
