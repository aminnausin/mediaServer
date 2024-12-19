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
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->timestamp('created_at')->useCurrent()->nullable();
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->timestamp('created_at')->useCurrent()->nullable();
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
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('created_at');
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn('created_at');
        });
    }
};
