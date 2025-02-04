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
            $table->index('is_private'); // Index for filtering
        });
        Schema::table('playbacks', function (Blueprint $table) {
            $table->index('progress'); // Index for sorting
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->index('status'); // Index for filtering
        });
        Schema::table('sub_tasks', function (Blueprint $table) {
            $table->index('status'); // Index for filtering
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['is_private']);
        });

        Schema::table('playbacks', function (Blueprint $table) {
            $table->dropIndex(['progress']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('sub_tasks', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
