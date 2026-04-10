<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('downloads_enabled')->default(false);
            $table->boolean('downloads_require_auth')->default(true);
        });

        Schema::table('series', function (Blueprint $table) {
            $table->boolean('downloads_enabled')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['downloads_enabled', 'downloads_require_auth']);
        });

        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn('downloads_enabled');
        });
    }
};
