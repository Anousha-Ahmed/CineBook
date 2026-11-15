<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The application stores premium_rows as a count (see Screen model cast,
        // ScreenRequest and Seeder), so the column must accept integers.
        Schema::table('screens', function (Blueprint $table) {
            $table->unsignedInteger('premium_rows')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('screens', function (Blueprint $table) {
            $table->json('premium_rows')->nullable()->change();
        });
    }
};
