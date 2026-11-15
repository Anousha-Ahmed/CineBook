<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_session_id')->nullable();
            $table->string('stripe_payment_intent')->nullable();
            $table->integer('amount_cents');
            $table->enum('status', ['pending', 'successful', 'failed', 'refunded'])->default('pending');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payments'); }
};