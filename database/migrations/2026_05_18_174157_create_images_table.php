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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('metadata_uuid')->constrained('metadata', 'uuid')->cascadeOnDelete();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->smallInteger('image_type');
            $table->smallInteger('image_source');
            $table->boolean('is_primary')->default(false);

            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('blurhash', 64)->nullable();

            $table->text('path')->nullable();
            $table->text('source_url')->nullable();

            $table->timestampsTz();

            $table->index(['metadata_uuid', 'image_type']);
        });

        DB::statement('CREATE UNIQUE INDEX images_primary_unique ON images (metadata_uuid, image_type) WHERE is_primary = true');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('images');
    }
};
