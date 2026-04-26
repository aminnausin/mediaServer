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
        Schema::create('series_size_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('total_bytes');
            $table->unsignedInteger('file_count');
            $table->timestampTz('recorded_at');
            $table->timestamptz('created_at')->default(now());
        });

        DB::statement('
            INSERT INTO series_size_histories (series_id, total_bytes, file_count, recorded_at)
            SELECT
                id,
                total_size,
                GREATEST(COALESCE(file_count, 0), COALESCE(episodes, 0)),
                updated_at AS recorded_at
            FROM series
            WHERE total_size IS NOT NULL
            AND folder_id IS NOT NULL;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('series_size_histories');
    }
};
