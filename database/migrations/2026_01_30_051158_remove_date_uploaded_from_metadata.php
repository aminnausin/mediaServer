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
            $table->dropColumn('date_uploaded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('metadata', function (Blueprint $table) {
            $table->timestamp('date_uploaded')->nullable();
        });

        DB::table('metadata')->whereNotNull('file_modified_at')->update(['date_uploaded' => DB::raw("file_modified_at AT TIME ZONE 'UTC'")]);
    }
};
