<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('server_config', function (Blueprint $table) {
            $table->string("key")->primary();
            $table->text('value');
            $table->text('default_value');
            $table->enum("type", ['string', 'boolean', 'integer', 'array', 'float']);
            $table->enum('group', ['scanning', 'metadata', 'media', 'performance', 'storage'])->index();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('server_config');
    }
};
