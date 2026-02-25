<?php

namespace App\Models;

use App\Enums\MediaType;
use App\Traits\HasEditableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Series extends Model {
    use HasEditableFields, HasFactory;

    /**
     * id                   -> int8 (pk) (index)
     * folder_id            -> int8 (fk) (nullable) (index)
     * composite_id         -> varchar(255) (index)
     *
     * title                -> varchar(255) (nullable)
     * description          -> text (nullable)
     * studio               -> varchar(255) (nullable)
     * rating               -> int2 (nullable)
     * seasons              -> int4 (nullable)
     * episodes             -> int4 (nullable)
     * films                -> int4 (nullable)
     * thumbnail_url        -> varchar(255) (nullable)
     * editor_id            -> int8 (fk) (nullable)
     *
     * created_at           -> timestamp (nullable)
     * updated_at           -> timestamp (nullable)
     *
     * total_size           -> int8
     * raw_thumbnail_url    -> varchar(255) (nullable)
     * primary_media_type   -> int2 (enum)
     *
     * edited_at            -> timestamp (nullable)
     * started_at           -> date (nullable)
     * ended_at             -> date (nullable)
     *
     * file_count           -> uint4 (default=0)
     *
     * avg_intro_duration   -> float(2) (default=90)
     */
    protected $fillable = [
        'folder_id',
        'editor_id',
        'composite_id',
        'title',
        'description',
        'studio',
        'rating',
        'seasons',
        'episodes',
        'films',
        'avg_intro_duration',
        'started_at',
        'ended_at',
        'file_count',
        'thumbnail_url',
        'edited_at',
    ];

    protected $casts = [
        'primary_media_type' => MediaType::class,
        'avg_intro_duration' => 'float',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',

        'edited_at' => 'datetime',
    ];

    public function folder(): BelongsTo {
        return $this->belongsTo(Folder::class);
    }

    public function editor(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function folderTags(): HasMany {
        return $this->hasMany(FolderTag::class);
    }

    public function sizeHistory(): HasMany {
        return $this->hasMany(SeriesSizeHistory::class);
    }

    protected function getEditableFields(): array {
        return [
            'editor_id',
            'title',
            'description',
            'studio',
            'rating',
            'seasons',
            'episodes',
            'films',
            'avg_intro_duration',
            'started_at',
            'ended_at',
            'thumbnail_url',
        ];
    }
}
