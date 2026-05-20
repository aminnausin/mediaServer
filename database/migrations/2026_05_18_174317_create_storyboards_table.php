<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('storyboards', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('metadata_uuid')->constrained('metadata', 'uuid')->cascadeOnDelete()->unique();

            $table->smallInteger('tile_rows');
            $table->smallInteger('tile_cols');
            $table->smallInteger('tile_width');
            $table->smallInteger('tile_height');
            $table->smallInteger('tile_count');

            $table->integer('interval_ms');

            $table->timestampsTz();
            $table->timestampTz('modified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('storyboards');
    }
};
