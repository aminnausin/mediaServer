<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        DB::table('server_configs')->where('key', 'supported_extentions')->update(['key' => 'supported_extensions']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        DB::table('server_configs')->where('key', 'supported_extensions')->update(['key' => 'supported_extentions']);
    }
};
