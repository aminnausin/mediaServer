<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('folder_tags', function (Blueprint $table) {
            $table->unique(['tag_id', 'series_id']);
        });

        Schema::table('video_tags', function (Blueprint $table) {
            $table->unique(['tag_id', 'metadata_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('folder_tags', function (Blueprint $table) {
            $table->dropUnique(['tag_id', 'series_id']);
        });

        Schema::table('video_tags', function (Blueprint $table) {
            $table->dropUnique(['tag_id', 'metadata_id']);
        });
    }
};
