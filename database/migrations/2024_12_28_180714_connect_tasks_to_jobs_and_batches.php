<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('batch_id')->nullable()->after('user_id');
            $table->foreign('batch_id')->references('id')->on('job_batches')->nullOnDelete();
            $table->unsignedSmallInteger('total_sub_tasks')->default(0)->after('status');
            $table->dropColumn('sub_tasks');
            // $table->dropColumn('sub_tasks_pending');
            // $table->dropColumn('sub_tasks_complete');
            // $table->dropColumn('sub_tasks_failed');
        });

        Schema::table('sub_tasks', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
            $table->unsignedSmallInteger('sub_tasks')->default(0);
            // $table->unsignedSmallInteger('sub_tasks_pending')->default(0);
            // $table->unsignedSmallInteger('sub_tasks_complete')->default(0);
            // $table->unsignedSmallInteger('sub_tasks_failed')->default(0);
        });

        Schema::table('sub_tasks', function (Blueprint $table) {
        });
    }
};
