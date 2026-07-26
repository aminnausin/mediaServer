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
            $table->index(['folder_id', 'created_at']); // Resolve by folder and by date
        });

        Schema::table('metadata', function (Blueprint $table) {
            $table->index(['video_id', 'created_at']); // Order videos by metadata_created_at
        });

        Schema::table('storyboards', function (Blueprint $table) {
            $table->index(['metadata_uuid']); // Resolve by metadata_uuid
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->index(['category_id']); // Resolve by category
        });

        Schema::table('series', function (Blueprint $table) {
            $table->index(['title']); // Resolve by name
            $table->index(['primary_media_type']); // Resolve by type
        });

        DB::statement('CREATE INDEX series_created_at_feed_index ON series (created_at DESC) WHERE file_count > 0'); // Recently added
        DB::statement('CREATE INDEX series_started_at_feed_index ON series (started_at DESC) WHERE file_count > 0 AND started_at IS NOT NULL'); // Recently released
        DB::statement('CREATE INDEX playback_progress_continue_watching_index ON playback_progress (user_id, updated_at DESC) WHERE progress_percentage < 100;');
        DB::statement('CREATE INDEX metadata_created_at_feed_index ON metadata (created_at DESC) WHERE created_at IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropIndex(['folder_id', 'created_at']);
        });

        Schema::table('metadata', function (Blueprint $table) {
            $table->dropIndex(['video_id', 'created_at']);
            $table->dropIndex('metadata_created_at_feed_index');
        });

        Schema::table('storyboards', function (Blueprint $table) {
            $table->dropIndex(['metadata_uuid']);
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
        });

        Schema::table('series', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['primary_media_type']);
            $table->dropIndex('series_created_at_feed_index');
            $table->dropIndex('series_started_at_feed_index');
        });

        Schema::table('playback_progress', function (Blueprint $table) {
            $table->dropIndex('playback_progress_continue_watching_index');
        });
    }
};
