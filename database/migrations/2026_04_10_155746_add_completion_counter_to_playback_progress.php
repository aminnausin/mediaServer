<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('playback_progress', function (Blueprint $table) {
            $table->unsignedInteger('completion_count')->default(0);
            $table->timestamptz('last_completed_at')->nullable()->after('completion_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('playback_progress', function (Blueprint $table) {
            $table->dropColumn(['completion_count', 'last_completed_at']);
        });
    }
};
