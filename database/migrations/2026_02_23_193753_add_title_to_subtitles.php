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
        Schema::table('subtitles', function (Blueprint $table) {
            $table->text('title')->nullable();
        });

        DB::table('metadata')->whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('subtitles')
                ->whereColumn('subtitles.metadata_uuid', 'metadata.uuid');
        })->update(['subtitles_scanned_at' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('subtitles', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
};
