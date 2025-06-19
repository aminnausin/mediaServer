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
        'sub_tasks_current',
        'duration',
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
