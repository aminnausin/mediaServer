<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('server_configs', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->text('default_value')->nullable();
            $table->enum('type', ['string', 'bool', 'integer', 'array', 'float']);
            $table->enum('group', ['scanner', 'metadata', 'media', 'performance', 'storage'])->index();
            $table->timestampsTz();
        });

        Artisan::call('db:seed', [
            '--class' => 'ServerConfigSeeder',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('server_configs');
    }
};
