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
        Schema::create('showtimes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
        $table->foreignId('screen_id')->constrained()->cascadeOnDelete();
        $table->dateTime('start_time');
        $table->integer('base_price_cents'); // Price in cents (USD)
        $table->enum('status', ['scheduled', 'cancelled'])->default('scheduled');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showtimes');
    }
};
