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
            $table->unsignedBigInteger('default_folder_id')->nullable()->unique();
            $table->unsignedBigInteger('editor_id')->nullable();
            $table->foreign('default_folder_id')
                ->references('id')
                ->on('folders')
                ->nullOnDelete();
            $table->foreign('editor_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('default_folder_id');
            $table->dropColumn('editor_id');
        });
    }
};
