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
            $table->string('status')->default(TaskStatus::PENDING);
            $table->string('name')->nullable();
            $table->string('summary')->nullable();
            $table->unsignedSmallInteger('sub_tasks')->default(0);
            $table->unsignedSmallInteger('sub_tasks_pending')->default(0);
            $table->unsignedSmallInteger('sub_tasks_complete')->default(0);
            $table->unsignedSmallInteger('sub_tasks_failed')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sub_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tasks_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default(TaskStatus::PENDING);
            $table->string('name')->nullable();
            $table->unsignedInteger('model_count');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('sub_tasks');
    }
};
