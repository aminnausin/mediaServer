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
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('date')->nullable();
        });

        DB::statement("
            UPDATE videos v
            SET date = TO_CHAR(m.file_modified_at AT TIME ZONE 'UTC, 'YYYY-MM-DD HH12:MI AM')
            FROM (
                SELECT DISTINCT ON (video_id) video_id, file_modified_at
                FROM metadata
                WHERE file_modified_at IS NOT NULL
                ORDER BY video_id, file_modified_at DESC
            ) m
            WHERE m.video_id = v.id
        ");
    }
};
