<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('subtitles', function (Blueprint $table) {
            $table->dropForeign(['metadata_uuid']);

            $table->uuid('metadata_uuid')->nullable()->change();
            $table->foreign('metadata_uuid')
                ->references('uuid')
                ->on('metadata')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('subtitles', function (Blueprint $table) {
            $table->dropForeign(['metadata_uuid']);

            $table->uuid('metadata_uuid')->nullable(false)->change();
            $table->foreign('metadata_uuid')
                ->references('uuid')
                ->on('metadata')
                ->cascadeOnDelete();
        });
    }
};
