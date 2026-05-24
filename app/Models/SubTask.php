<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;

class SubTask extends Model {
    /**
     * id                   -> int8 (pk) (index)
     * task_id              -> int8 (fk)
     *
     * status               -> int2 (default=0) (index)
     * name                 -> varchar(255) (nullable) (index)
     *
     * summary              -> text (nullable)
     * progress             -> int2 (default=0)
     * duration             -> int8 (default=0)
     *
     * started_at           -> timestamp (nullable)
     * ended_at             -> timestamp (nullable)
     * created_at           -> timestamp (nullable)
     * updated_at           -> timestamp (nullable)
     *
     * reference_uuid       -> uuid (nullable)
     * reference_type       -> varchar(255) (nullable) (composite index with reference_uuid and status)
     */
    protected $fillable = [
        'task_id',
        'status',
        'name',
        'summary',
        'progress',
        'duration',
        'started_at',
        'ended_at',
        'reference_uuid',
        'reference_type',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
    ];

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
