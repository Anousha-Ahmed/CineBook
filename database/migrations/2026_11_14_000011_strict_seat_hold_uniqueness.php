<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep only the newest hold per seat before enforcing the stricter constraint.
        DB::statement('DELETE FROM seat_holds WHERE id NOT IN (SELECT id FROM (SELECT MAX(id) AS id FROM seat_holds GROUP BY showtime_id, seat_id) AS keep)');

        Schema::table('seat_holds', function (Blueprint $table) {
            $table->unique(['showtime_id', 'seat_id']);
        });
    }

    public function down(): void
    {
        Schema::table('seat_holds', function (Blueprint $table) {
            $table->dropUnique(['showtime_id', 'seat_id']);
        });
    }
};
