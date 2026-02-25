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
        Schema::table('metadata', function (Blueprint $table) {
            $table->timestampTz('first_file_modified_at')->nullable();
        });

        DB::table('metadata')->whereNull('first_file_modified_at')->whereNotNull('file_modified_at')->update(['first_file_modified_at' => DB::raw('file_modified_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('metadata', function (Blueprint $table) {
            $table->dropColumn('first_file_modified_at');
        });
    }
};
