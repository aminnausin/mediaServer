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
            $table->boolean('allow_downloads')->default(false);
            $table->boolean('require_login_for_downloads')->default(true);
        });

        Schema::table('series', function (Blueprint $table) {
            $table->boolean('allow_downloads')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['allow_downloads', 'require_login_for_downloads']);
        });

        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn('allow_downloads');
        });
    }
};
