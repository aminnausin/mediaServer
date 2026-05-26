<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('storyboards', function (Blueprint $table) {
            $table->text('command')->nullable();
            $table->float('generation_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('storyboards', function (Blueprint $table) {
            $table->dropColumn(['command', 'generation_time']);
        });
    }
};
