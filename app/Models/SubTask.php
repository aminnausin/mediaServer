<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TaskStatus;

class SubTask extends Model {
    protected $fillable = [
        'task_id',
        'name',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
    ];

    public function task() {
        return $this->belongsTo(Task::class);
    }
}
