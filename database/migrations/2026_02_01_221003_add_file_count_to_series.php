<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('series', function (Blueprint $table) {
            $table->unsignedInteger('file_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn('file_count');
        });
    }
};
