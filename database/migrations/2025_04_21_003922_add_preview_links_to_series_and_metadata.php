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
            //
            $table->string('raw_thumbnail_url')->nullable();
        });
        Schema::table('metadata', function (Blueprint $table) {
            $table->string('raw_thumbnail_url')->nullable();
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn('raw_thumbnail_url');
            //
        });
        Schema::table('metadata', function (Blueprint $table) {
            $table->dropColumn('raw_thumbnail_url');
            //
        });
    }
};
