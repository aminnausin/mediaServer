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
            $table->unsignedInteger('resolution_width')->nullable();
            $table->unsignedInteger('resolution_height')->nullable();
            $table->unsignedInteger('frame_rate')->nullable();
            $table->unsignedBigInteger('bitrate')->nullable();
            $table->string('codec')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('metadata', function (Blueprint $table) {
            $table->dropColumn('resolution_width');
            $table->dropColumn('resolution_height');
            $table->dropColumn('frame_rate');
            $table->dropColumn('bitrate');
            $table->dropColumn('codec');
        });
    }
};
