<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('stripe_refund_id')->nullable()->after('stripe_payment_intent');
            $table->integer('refunded_amount_cents')->nullable()->after('amount_cents');
            $table->string('refund_status')->default('none')->after('status');
            $table->string('failure_reason')->nullable()->after('refund_status');
            $table->unsignedInteger('refund_attempts')->default(0)->after('failure_reason');
        });

        Schema::table('showtimes', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('status');
        });

        Schema::table('screens', function (Blueprint $table) {
            $table->unsignedInteger('rows')->nullable()->after('name');
            $table->unsignedInteger('seats_per_row')->nullable()->after('rows');
            $table->json('premium_rows')->nullable()->after('seats_per_row');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['action', 'created_at']);
            $table->index(['target_type', 'target_id']);
        });

        Schema::table('seat_holds', function (Blueprint $table) {
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('seat_holds', function (Blueprint $table) {
            $table->dropIndex(['expires_at']);
        });

        Schema::dropIfExists('audit_logs');

        Schema::table('screens', function (Blueprint $table) {
            $table->dropColumn(['rows', 'seats_per_row', 'premium_rows']);
        });

        Schema::table('showtimes', function (Blueprint $table) {
            $table->dropColumn('cancelled_at');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['stripe_refund_id', 'refunded_amount_cents', 'refund_status', 'failure_reason', 'refund_attempts']);
        });
    }
};
