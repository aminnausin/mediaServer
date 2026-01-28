<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('series', function (Blueprint $table) {
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
        });

        DB::statement('
            UPDATE series
            SET started_at = date_start,
                ended_at = date_end;
        ');

        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn(['date_start', 'date_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('series', function (Blueprint $table) {
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
        });

        DB::statement('
            UPDATE series
            SET date_start = started_at,
                date_end = ended_at
        ');

        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'ended_at']);
        });
    }
};
