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
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folder_id')->nullable()->unique();
            $table->string('composite_id')->unique(); // This keeps a record of linked folder if database entry is deleted and recreated at the same path. Should prune like once in a while maybe or keep data for archival
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('studio')->nullable();
            $table->decimal('rating')->nullable();
            $table->integer('seasons')->nullable();
            $table->integer('episodes')->nullable();
            $table->integer('films')->nullable();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->unsignedBigInteger('editor_id')->nullable();
            $table->foreign('folder_id')
                ->references('id')
                ->on('folders')
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
        Schema::dropIfExists('series');
    }
};
