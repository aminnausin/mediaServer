<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('playback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('metadata_id')->nullable();
            $table->foreign('metadata_id')
                ->references('id')
                ->on('metadata')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playback');
    }
};
