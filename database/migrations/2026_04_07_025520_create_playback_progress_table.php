<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('playback_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('metadata_id')->constrained()->cascadeOnDelete();
            $table->foreignId('record_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('progress_offset');
            $table->timestampsTz();

            $table->unique(['metadata_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('playback_progress');
    }
};
