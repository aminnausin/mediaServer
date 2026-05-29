<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->uuidMorphs('imageable');

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->smallInteger('image_type');
            $table->smallInteger('image_source');
            $table->string('image_variant', 32)->nullable();

            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('blur_hash', 64)->nullable();

            $table->string('format', 10)->nullable();

            $table->text('path')->nullable();
            $table->text('source_url')->nullable();

            $table->timestampTz('replaced_at')->nullable();

            $table->timestampsTz();

            $table->index(['imageable_type', 'imageable_id', 'image_type', 'replaced_at']);
        });

        Schema::table('metadata', function (Blueprint $table) {
            $table->foreignId('primary_poster_id')->nullable()->constrained('images')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('metadata', function (Blueprint $table) {
            $table->dropForeign(['primary_poster_id']);
            $table->dropColumn('primary_poster_id');
        });

        Schema::dropIfExists('images');
    }
};
