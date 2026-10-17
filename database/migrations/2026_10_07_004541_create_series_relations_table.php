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
        Schema::create('series_relations', function (Blueprint $table) {
            $table->foreignId('series_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_series_id')->constrained('series')->cascadeOnDelete();
            $table->string('type'); // only storable types from SeriesRelationType
            $table->timestampsTz();

            $table->primary(['series_id', 'related_series_id']);
            $table->index('related_series_id');
        });

        DB::statement('ALTER TABLE series_relations ADD CONSTRAINT series_relations_no_self  CHECK (series_id <> related_series_id)');
        DB::statement('CREATE UNIQUE INDEX series_relations_pair_unique ON series_relations (LEAST(series_id, related_series_id), GREATEST(series_id, related_series_id))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('series_relations');
    }
};
