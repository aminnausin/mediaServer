<?php

use App\Enums\TaskStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->nullOnDelete();
            $table->string('batch_id')->nullable();
            $table->tinyInteger('status')->default(TaskStatus::PENDING);
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->text('summary')->nullable();
            $table->text('url')->nullable();
            $table->unsignedSmallInteger('sub_tasks_total')->default(0);
            $table->unsignedSmallInteger('sub_tasks_pending')->default(0);
            $table->unsignedSmallInteger('sub_tasks_complete')->default(0);
            $table->unsignedSmallInteger('sub_tasks_failed')->default(0);
            $table->unsignedBigInteger('duration')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->foreign('batch_id')->references('id')->on('job_batches')->nullOnDelete();
        });

        Schema::create('sub_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('status')->default(TaskStatus::PENDING);
            $table->string('name')->nullable();
            $table->text('summary')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->unsignedBigInteger('duration')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('sub_tasks');
        Schema::dropIfExists('tasks');
    }
};
