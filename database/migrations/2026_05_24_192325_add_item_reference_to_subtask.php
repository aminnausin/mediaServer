<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('sub_tasks', function (Blueprint $table) {
            $table->uuid('reference_uuid')->nullable();
            $table->string('reference_type')->nullable();
            $table->index(['reference_uuid', 'reference_type', 'status']);
            $table->index(['task_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('sub_tasks', function (Blueprint $table) {
            $table->dropIndex(['reference_uuid', 'reference_type', 'status']);
            $table->dropIndex(['task_id']);
            $table->dropColumn(['reference_uuid', 'reference_type']);
        });
    }
};
