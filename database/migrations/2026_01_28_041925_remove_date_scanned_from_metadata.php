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
            $table->dropColumn("date_scanned");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('metadata', function (Blueprint $table) {
            $table->date("date_scanned")->nullable();
        });

        DB::table('metadata')->whereNotNull('file_scanned_at')->update(['date_scanned' => DB::raw('DATE(file_scanned_at)')]);
    }
};
