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
        Schema::create('library_size_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('total_bytes');
            $table->unsignedInteger('folder_count');
            $table->unsignedInteger('file_count');
            $table->timestampTz('recorded_at');
            $table->timestamptz('created_at')->default(now());
        });

        DB::statement('
            INSERT INTO library_size_histories (category_id, total_bytes, folder_count, file_count, recorded_at)
            SELECT
                c.id AS category_id,
                COALESCE(SUM(s.total_size), 0) AS total_bytes,
                COUNT(DISTINCT f.id) AS folder_count,
                COALESCE(SUM(GREATEST(COALESCE(s.file_count, 0), COALESCE(s.episodes, 0))), 0) AS file_count,
                COALESCE(MAX(s.updated_at), NOW()) AS recorded_at
            FROM categories c
            LEFT JOIN folders f ON f.category_id = c.id
            LEFT JOIN series s ON s.folder_id = f.id
            GROUP BY c.id;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('library_size_histories');
    }
};
