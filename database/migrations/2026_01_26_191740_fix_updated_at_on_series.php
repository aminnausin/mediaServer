<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        /**
         *
         * Historical data correction
         *
         * A past bug caused `series.updated_at` to change on every scan
         *
         * This fixes that data corruption by setting `updated_at` to the
         * newest `created_at` of the videos (media) belonging to the series
         *
         * Only applies to rows where `editor_id IS NULL` (this labels possible erroneous data)
         * Does not change series rows if they have no related videos (media)
         *
         */

        DB::beginTransaction();

        $rows = DB::update("
            UPDATE series s
            SET updated_at = COALESCE(v.max_created_at, s.updated_at)
            FROM (
                SELECT f.id AS folder_id, MAX(v.created_at) AS max_created_at
                FROM videos v
                JOIN folders f ON f.id = v.folder_id
                GROUP BY f.id
            ) v
            WHERE v.folder_id = s.folder_id
            AND s.editor_id IS NULL
            AND s.updated_at > v.max_created_at
        ");

        Log::info('Series timestamp correction migration executed', [
            'rows_affected' => $rows,
        ]);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //
    }
};
