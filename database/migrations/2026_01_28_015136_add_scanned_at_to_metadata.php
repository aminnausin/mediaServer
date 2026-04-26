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
            $table->timestampTz('file_scanned_at')->nullable();
        });

        DB::table('metadata')->whereNotNull('date_scanned')->update(['file_scanned_at' => DB::raw('date_scanned')]); // No timestamp available because date_scanned was just a date field
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('metadata', function (Blueprint $table) {
            $table->dropColumn('file_scanned_at');
        });
    }
};
