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
        Schema::table('playback_progress', function (Blueprint $table) {
            $table->unsignedTinyInteger('progress_percentage')->default(0);
        });

        DB::statement('
            UPDATE playback_progress pp
            SET progress_percentage = COALESCE(
                LEAST(100, GREATEST(0, ROUND(pp.progress_offset * 100.0 / NULLIF(m.duration, 0), 0))),
                0
            )
            FROM metadata m
            WHERE m.id = pp.metadata_id
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('playback_progress', function (Blueprint $table) {
            $table->dropColumn('progress_percentage');
        });
    }
};
