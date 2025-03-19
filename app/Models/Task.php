<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model {
    protected $fillable = [
        'user_id',
        'batch_id',
        'status',
        'name',
        'summary',
        'description',
        'sub_tasks_total',
        'sub_tasks_pending',
        'sub_tasks_complete',
        'sub_tasks_failed',
        'duration',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
    ];

    // Boot method to register model events
    // protected static function boot() {
    // parent::boot();

    // static::updated(function ($task) {
    //     // This will run whenever the task is updated
    //     // Add your custom logic here
    //     $task->updateDuration();
    // });
    // }

    // public function updateDuration() {
    //     if ($this->started_at && $this->ended_at) {
    //         $this->duration = $this->ended_at->diffInSeconds($this->started_at);
    //         $this->save();
    //     }
    // }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function sub_tasks(): HasMany {
        return $this->hasMany(SubTask::class);
    }
}
