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
        Schema::table('playback_progress', function (Blueprint $table) {
            $table->dropUnique(['metadata_id', 'user_id']);

            $table->foreignId('user_id')->nullable()->change();
            $table->uuid('guest_token')->nullable()->index();
        });

        DB::statement('CREATE UNIQUE INDEX progress_user_unique ON playback_progress (user_id, metadata_id) WHERE user_id IS NOT NULL');
        DB::statement('CREATE UNIQUE INDEX progress_guest_unique ON playback_progress (guest_token, metadata_id) WHERE guest_token IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('playback_progress', function (Blueprint $table) {
            $table->dropIndex('progress_guest_unique');
            $table->dropIndex('progress_user_unique');
            $table->dropColumn('guest_token');
            $table->foreignId('user_id')->nullable(false)->change();
            $table->unique(['metadata_id', 'user_id']);
        });
    }
};
