<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model {
    protected $fillable = [
        'user_id',
        'name',
        'summary',
        'status',
        'sub_tasks',
        'sub_tasks_pending',
        'sub_tasks_completed',
        'sub_tasks_failed',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function sub_tasks(): HasMany {
        return $this->hasMany(SubTask::class);
    }
}
