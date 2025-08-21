<?php

use App\Enums\MediaType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('series', function (Blueprint $table) {
            $table->tinyInteger('primary_media_type')->default(MediaType::VIDEO);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn('primary_media_type');
        });
    }
};
