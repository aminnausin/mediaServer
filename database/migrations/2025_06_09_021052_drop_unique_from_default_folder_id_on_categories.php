<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_default_folder_id_unique');
        });
    }

    public function down(): void {
        Schema::table('categories', function (Blueprint $table) {
            $table->unique('default_folder_id');
        });
    }
};
