<?php

namespace App\Models;

use App\Services\Subtitles\SubtitlePath;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subtitle extends Model {
    use HasFactory;

    /**
     * id                   -> int8 (pk) (index)
     * metadata_uuid        -> uuid (fk) (index) (onDelete=setNull)
     * track_id             -> int2
     *
     * language             -> varchar(16) (nullable)
     * title                -> text (nullable)
     * codec                -> varchar(32) (nullable)
     * format               -> varchar(16) (nullable)
     *
     * path                 -> varchar(255) (nullable)
     * external_path        -> text (nullable)
     *
     * is_default           -> boolean (default=false)
     * is_forced            -> boolean (default=false)
     *
     * source_key           -> text (composite index with uuid)
     *
     * created_at           -> timestamp (nullable)
     * updated_at           -> timestamp (nullable)
     */
    protected $fillable = [
        'track_id',
        'language',
        'title',
        'codec',
        'format',
        'path',
        'is_default',
        'is_forced',
    ];

    public function metadata(): BelongsTo {
        return $this->belongsTo(Metadata::class, 'uuid', 'metadata_uuid');
    }

    public function getDirectoryPath(): string {
        return SubtitlePath::buildDirectory($this->metadata_uuid);
    }

    public function getFilePath(string $format, ?string $language = null): string {
        return SubtitlePath::buildFilePath($this->metadata_uuid, $this->track_id, $format, $language);
    }

    public static function getVisibleFields(): array {
        return ['id', 'track_id', 'metadata_uuid', 'language', 'title', 'codec', 'is_default', 'is_forced'];
    }
}
