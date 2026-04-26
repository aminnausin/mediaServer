<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('metadata', function (Blueprint $table) {
            // generated column that strips the extention off of composite id
            $table->text('logical_composite_id')->storedAs("regexp_replace(composite_id, '\\.[^.]+$', '')")->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('metadata', function (Blueprint $table) {
            $table->dropColumn('logical_composite_id');
        });
    }
};
