<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaybackProgress extends Model {
    use HasFactory;
    /**
     * id                   -> int8 (pk) (index)
     * user_id              -> int8 (fk) (index) (unique with metadata_id)
     * metadata_id          -> int8 (fk) (index) (unique with user_id)
     * record_id            -> int8 (fk) (nullable)
     *
     * progress_offset      -> int4 (in seconds)
     * progress_percentage  -> int1 (0-100) (default=0)
     *
     * completion_count     -> int4 (default=0)
     * last_completed_at    -> timestampTz (nullable)
     *
     * created_at           -> timestampTz (nullable)
     * updated_at           -> timestampTz (nullable)
     */
    protected $fillable = [
        'user_id',
        'metadata_id',
        'record_id',
        'progress_offset',
        'progress_percentage',
    ];

    protected $attributes = [
        'progress_percentage' => 0,
        'completion_count' => 0,
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function metadata(): BelongsTo {
        return $this->belongsTo(Metadata::class);
    }

    public function record(): BelongsTo {
        return $this->belongsTo(Record::class);
    }
}
