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

    public $timestamps = false;

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
        return $this->videos()->join('metadata', 'videos.id', '=', 'metadata.video_id')->sum('metadata.file_size');
    }

    public function getPrimaryMediaTypeAttribute() {
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
}
