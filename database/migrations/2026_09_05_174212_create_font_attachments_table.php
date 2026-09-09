<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('font_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('metadata_uuid')->nullable()->constrained('metadata', 'uuid')->nullOnDelete();

            $table->string('file_name');
            $table->string('codec', 32)->nullable();
            $table->string('mime_type');
            $table->unsignedBigInteger('size')->default(0);

            $table->text('path')->nullable();

            $table->string('hash', 64)->nullable();

            $table->timestamps();

            $table->unique(['metadata_uuid', 'file_name']);
        });

        Schema::table('metadata', function (Blueprint $table) {
            $table->timestampTz('fonts_scanned_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('metadata', function (Blueprint $table) {
            $table->dropColumn(['fonts_scanned_at']);
        });

        Schema::dropIfExists('font_attachments');
    }
};
