<?php

namespace App\Models;

use App\Enums\MediaType;
use App\Traits\HasEditableFields;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Metadata extends Model {
    use HasEditableFields, HasFactory;

    protected $fillable = [
        'uuid',
        'composite_id',
        'video_id',
        'editor_id',
        'title',
        'description',
        'lyrics',
        'duration',
        'episode',
        'season',
        'view_count',
        'file_size',
        'mime_type',
        'codec',
        'bitrate',
        'resolution_width',
        'resolution_height',
        'frame_rate',
        'poster_url',
        'date_released',
        'date_scanned',
        'date_uploaded',
        'media_type',
        'album',
        'artist',
    ];

    protected $casts = [
        'date_uploaded' => 'datetime',
        'media_type' => MediaType::class,
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

    public function getDateReleasedFormattedAttribute() {
        return $this->attributes['date_released'] ? Carbon::parse($this->attributes['date_released'])->format('F d, Y') : null;
    }

    protected function getEditableFields(): array {
        return [
            'editor_id',
            'title',
            'description',
            'lyrics',
            'episode',
            'season',
            'poster_url',
            'album',
            'artist',
        ];
    }
}
