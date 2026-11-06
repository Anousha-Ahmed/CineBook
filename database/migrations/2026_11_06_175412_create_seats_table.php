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
        Schema::create('seats', function (Blueprint $table) {
        $table->id();
        $table->foreignId('screen_id')->constrained()->cascadeOnDelete();
        $table->string('row'); 
        $table->integer('number'); 
        $table->boolean('is_premium')->default(false);
        $table->boolean('is_disabled')->default(false);
        $table->timestamps();
        
        // Ek screen mein ek seat ek hi baar ho sakti hai, isliye unique constraint lagaya
        $table->unique(['screen_id', 'row', 'number']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
