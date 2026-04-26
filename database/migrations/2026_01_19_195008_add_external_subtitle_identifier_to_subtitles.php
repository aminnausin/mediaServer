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
        Schema::table('subtitles', function (Blueprint $table) {
            $table->text('external_path')->nullable();
            $table->text('source_key')->nullable();
        });

        DB::statement("
            UPDATE subtitles
            SET source_key =
                CASE
                    WHEN track_id > 0 THEN 'embedded:' || track_id
                    ELSE 'external:' || COALESCE(external_path, 'unknown')
                END
        ");

        Schema::table('subtitles', function (Blueprint $table) {
            $table->text('source_key')->nullable(false)->change();
        });

        Schema::table('subtitles', function (Blueprint $table) {
            $table->dropUnique(['metadata_uuid', 'track_id']);
            $table->unique(['metadata_uuid', 'source_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('subtitles', function (Blueprint $table) {
            $table->dropUnique(['metadata_uuid', 'source_key']);
        });

        Schema::table('subtitles', function (Blueprint $table) {
            $table->unique(['metadata_uuid', 'track_id']);
        });

        Schema::table('subtitles', function (Blueprint $table) {
            $table->dropColumn(['external_path', 'source_key']);
        });
    }
};
