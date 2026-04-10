<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Folder extends Model {
    use HasFactory;

    /**
     * id                   -> int8 (pk) (index)
     * category_id          -> int8 (fk)
     *
     * name                 -> varchar(255)
     * path                 -> varchar(255) (index) (unique)
     *
     * created_at           -> timestamp (nullable)
     *
     * last_scan            -> int8 (default=0) (unused)
     */
    public $timestamps = false;

    protected $casts = [
        'total_size' => 'integer',
        'created_at' => 'datetime',
    ];

    public function videos(): HasMany {
        return $this->hasMany(Video::class);
    }

    public function series(): HasOne {
        return $this->hasOne(Series::class);
    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function folderTags(): HasManyThrough {
        return $this->hasManyThrough(FolderTag::class, Series::class);
    }

    public function getTotalSizeAttribute() {
        return (int) $this->videos()->join('metadata', 'videos.id', '=', 'metadata.video_id')->sum('metadata.file_size');
    }

    public function getPrimaryMediaTypeAttribute(): int {
        return $this->isMajorityAudio() ? 1 : 0;
    }

    public function isMajorityAudio(): bool {
        $counts = $this->videos()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN metadata.mime_type LIKE 'audio%' THEN 1 ELSE 0 END) as audio")
            ->join('metadata', 'videos.id', '=', 'metadata.video_id')
            ->first();

        return $counts->total > 0 && $counts->audio >= ($counts->total / 2);
    }

    public function downloadsEnabled(): bool {
        if (! $this->category?->downloadsEnabled()) {
            return false;
        }

        return $this->series?->downloads_enabled ?? false;
    }
}
