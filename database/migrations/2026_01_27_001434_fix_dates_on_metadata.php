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
         * Historical data correction
         *
         * A past bug set `metadata.created_at` and in some cases `metadata.updated_at` to NULL during scans
         * This migration restores those timestamps using the best available related data
         *
         * Essentially, if metadata.created_at is empty, set it to earliest of video.created_at if exists and metadata.updated_at
         *
         * In addition, backfill updated_at with created_at if empty
         */
        DB::beginTransaction();

        try {
            /**
             * Backfill created_at from earliest of video.created_at or updated_at
             */
            $backfillWithVideo = DB::update('
                UPDATE metadata m
                SET created_at = CASE
                    WHEN m.updated_at IS NOT NULL
                        THEN LEAST (v.created_at, m.updated_at)
                    ELSE v.created_at
                END
                FROM videos v
                WHERE m.video_id = v.id
                    AND m.created_at IS NULL
                    AND v.created_at IS NOT NULL
            ');

            /**
             * Backfill created_at from updated_at when no video exists
             */
            $backfillWithoutVideo = DB::update('
                UPDATE metadata
                SET created_at = updated_at
                WHERE video_id IS NULL
                    AND created_at IS NULL
                    AND updated_at IS NOT NULL
            ');

            /**
             * Backfill updated_at from created_at
             */
            $backfillUpdatedAt = DB::update('
                UPDATE metadata
                SET updated_at = created_at
                WHERE updated_at IS NULL
                    AND created_at IS NOT NULL;
            ');

            Log::info('Metadata timestamp correction migration executed', [
                'rows_affected' => $backfillWithVideo + $backfillWithoutVideo + $backfillUpdatedAt,
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //
    }
};
