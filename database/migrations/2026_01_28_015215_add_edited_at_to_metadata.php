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
            $table->timestampTz('edited_at')->nullable();
        });

        DB::table('metadata')->whereNotNull('editor_id')->update(['edited_at' => DB::raw('updated_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('metadata', function (Blueprint $table) {
            $table->dropColumn('edited_at');
        });
    }
};
