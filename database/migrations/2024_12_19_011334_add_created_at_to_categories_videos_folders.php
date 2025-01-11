<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('categories', function (Blueprint $table) {
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->unsignedBigInteger('last_scan')->default(0);
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->timestamp('created_at')->useCurrent()->nullable();
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->unsignedBigInteger('last_scan')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('last_scan');
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('created_at');
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('last_scan');
        });
    }
};
