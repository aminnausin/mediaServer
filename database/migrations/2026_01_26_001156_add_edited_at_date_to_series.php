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

        DB::transaction(function () {
            Schema::table('series', function (Blueprint $table) {
                $table->timestamp("edited_at")->nullable();
            });

            // Essentially move updated_at values to edited at where editor_id is not null
            // and then set updated_at to max of series.folder.videos.created_at where editor_id is not null

            DB::statement("
                UPDATE series s
                SET edited_at = s.updated_at
                WHERE s.editor_id IS NOT NULL
            ");

            DB::statement("
                UPDATE series s
                SET updated_at = COALESCE(v.max_created_at, s.updated_at)
                FROM (
                    SELECT f.id AS folder_id, MAX(v.created_at) AS max_created_at
                    FROM videos v
                    JOIN folders f ON f.id = v.folder_id
                    WHERE v.created_at IS NOT NULL
                    GROUP BY f.id
                ) v
                WHERE v.folder_id = s.folder_id
                AND s.editor_id IS NOT NULL
                AND s.edited_at IS NOT NULL
            ");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn("edited_at");
        });
    }
};
