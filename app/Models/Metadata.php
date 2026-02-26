<?php

namespace App\Models;

use App\Enums\MediaType;
use App\Traits\HasEditableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Metadata extends Model {
    use HasEditableFields, HasFactory;

    /**
     * id                   -> int8 (pk) (index)
     * video_id             -> int8 (fk) (index) (nullable)
     * composite_id         -> varchar(255) (index)
     *
     * title                -> varchar(255) (nullable)
     * season               -> int4 (nullable)
     * episode              -> int4 (nullable)
     * duration             -> int4 (nullable)
     * view_count           -> int4 (nullable) (default=0)
     * description          -> text (nullable)
     * released_at          -> date (nullable)
     *
     * editor_id            -> int8 (fk) (nullable)
     * created_at           -> timestamp (nullable)
     * updated_at           -> timestamp (nullable)
     * uuid                 -> uuid (index) (nullable)
     *
     * file_size            -> int8 (nullable)
     * poster_url           -> text (nullable)
     * mime_type            -> varchar(255) (nullable)
     * captions             -> text (nullable)
     *
     * resolution_width     -> int4 (nullable)
     * resolution_height    -> int4 (nullable)
     * frame_rate           -> int4 (nullable)
     * bitrate              -> int8 (nullable)
     * codec                -> varchar(255) (nullable)
     * lyrics               -> text (nullable)
     *
     * raw_thumbnail_url    -> varchar(255) (nullable)
     * media_type           -> int2 (enum) (default=0)
     * artist               -> varchar(255) (nullable)
     * album                -> varchar(255) (nullable)
     *
     * subtitles_scanned_at -> timestamp (nullable)
     * logical_composite_id -> text (index) (generated)
     *
     * edited_at            -> timestamptz (nullable)
     * file_scanned_at      -> timestamptz (nullable)
     *
     * file_modified_at     -> timestamptz (nullable)
     *
     * intro_start          -> float(2) (nullable)
     * intro_duration       -> float(2) (nullable)
     *
     * first_file_modified_at     -> timestamptz (nullable)
     */
    protected $fillable = [
        // Id
        'uuid',
        'composite_id',

        // Fk
        'video_id',
        'editor_id',

        // User Editable
        'title',
        'description',
        'lyrics',
        'episode',
        'season',
        'poster_url',
        'album',
        'artist',
        'intro_start',
        'intro_duration',
        'released_at',

        // FFmpeg Generated
        'duration',
        'file_size',
        'mime_type',
        'codec',
        'bitrate',
        'resolution_width',
        'resolution_height',
        'frame_rate',
        'media_type',

        // API Editable
        'view_count',

        // Date Id
        'file_scanned_at',
        'file_modified_at',
        'first_file_modified_at',
        'edited_at',
    ];

    protected $guarded = ['logical_composite_id'];

    protected $casts = [
        'file_scanned_at' => 'datetime',
        'file_modified_at' => 'datetime',
        'first_file_modified_at' => 'datetime',

        'edited_at' => 'datetime',
        'media_type' => MediaType::class,

        'intro_start' => 'float',
        'intro_duration' => 'float',
    ];

    public function video(): BelongsTo {
        return $this->belongsTo(Video::class);
    }

    public function editor(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function playbacks(): HasMany {
        return $this->hasMany(Playback::class);
    }

    public function videoTags(): HasMany {
        return $this->hasMany(VideoTag::class);
    }

    public function records(): HasMany {
        return $this->hasMany(Record::class);
    }

    public function subtitles(): HasMany {
        return $this->hasMany(Subtitle::class, 'metadata_uuid', 'uuid')->orderBy('track_id');
    }

    public function syncViewCountToRecords(): void {
        $actual = $this->records()->count();
        self::where('id', $this->id)
            ->where('view_count', '<', $actual)
            ->update(['view_count' => $actual]);
    }

    // This is only for demo reset so it is not super important, only include fields that may have bad content
    protected function getEditableFields(): array {
        return [
            'editor_id',
            'title',
            'description',
            'lyrics',
            'poster_url',
            'album',
            'artist',
        ];
    }
}
