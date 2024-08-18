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
        Schema::create('metadata', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('video_id')->nullable();
            $table->unsignedBigInteger('editor_id')->nullable();
            $table->string('title')->nullable();
            $table->integer('season')->nullable();
            $table->integer('episode')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('view_count')->nullable();
            $table->string('description')->nullable();
            $table->string('tags')->nullable();
            $table->date('date_released')->nullable();
            $table->foreign('video_id')
                ->references('id')
                ->on('videos')
                ->nullOnDelete();
            $table->foreign('editor_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metadata');
    }
};