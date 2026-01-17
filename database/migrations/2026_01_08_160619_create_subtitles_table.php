<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('subtitles', function (Blueprint $table) {
            $table->id();

            $table->foreignUuid('metadata_uuid')->constrained('metadata', 'uuid')->cascadeOnDelete();
            $table->unsignedSmallInteger('track_id');
            $table->string('language', 16)->nullable();
            $table->string('codec', 32)->nullable();
            $table->string('format', 16)->nullable();
            $table->string('path')->nullable();

            $table->timestamps();

            $table->index(['metadata_uuid']);
            $table->unique(['metadata_uuid', 'track_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('subtitles');
    }
};
