<?php

namespace App\Models;

use App\Services\Subtitles\SubtitlePath;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subtitle extends Model {
    protected $fillable = [
        'track_id',
        'language',
        'codec',
        'format',
        'path',
    ];

    public function metadata(): BelongsTo {
        return $this->belongsTo(Metadata::class, 'uuid', 'metadata_uuid');
    }

    public function getDirectoryPath(): string {
        return SubtitlePath::directory($this->metadata_uuid);
    }

    public function getFilePath(string $format): string {
        return SubtitlePath::file($this->metadata_uuid, $this->track_id, $format);
    }
}
