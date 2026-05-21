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
        Schema::table('storyboards', function (Blueprint $table) {
            $table->integer("interval_seconds")->default(10);
        });

        DB::statement('UPDATE storyboards SET interval_seconds = interval_ms / 1000');

        Schema::table('storyboards', function (Blueprint $table) {
            $table->dropColumn("interval_ms");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('storyboards', function (Blueprint $table) {
            $table->integer("interval_ms")->default(10);
        });

        DB::statement('UPDATE storyboards SET interval_ms = interval_seconds * 1000');

        Schema::table('storyboards', function (Blueprint $table) {
            $table->dropColumn("interval_seconds");
        });
    }
};
