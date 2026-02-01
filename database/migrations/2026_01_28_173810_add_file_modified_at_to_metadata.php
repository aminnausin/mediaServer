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
            $table->timestampTz('file_modified_at')->nullable(); // TODO: eventually make this not nullable when enforced by api (currently you can initiate a metadata when you edit a media item that has not been verified)
        });

        DB::table('metadata')->whereNotNull('date_uploaded')->update(['file_modified_at' => DB::raw("date_uploaded AT TIME ZONE 'UTC'")]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('metadata', function (Blueprint $table) {
            $table->dropColumn('file_modified_at');
        });
    }
};
