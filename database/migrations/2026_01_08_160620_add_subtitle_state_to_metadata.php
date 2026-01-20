<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('metadata', function (Blueprint $table) {
            $table->timestamp('subtitles_scanned_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('metadata', function (Blueprint $table) {
            $table->dropColumn('subtitles_scanned_at');
        });
    }
};
