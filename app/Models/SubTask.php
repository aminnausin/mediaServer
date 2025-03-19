<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;

class SubTask extends Model {
    protected $fillable = [
        'task_id',
        'status',
        'name',
        'summary',
        'progress',
        'duration',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
    ];

    // protected static function boot() {
    // parent::boot();

    // static::updated(function ($subTask) {
    //     $subTask->updateDuration();
    // });
    // }

    public function updateDuration() {
        if (! isset($this->started_at)) {
            $this->duration = 0;
            $this->save;

            return;
        }

        $time = now();
        $this->duration = (int) $this->started_at->diffInSeconds($time);
        $this->save();
    }

    public function task() {
        return $this->belongsTo(Task::class);
    }
}
