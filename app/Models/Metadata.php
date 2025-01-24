<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Metadata extends Model {
    use HasFactory;

    protected $fillable = [
        'uuid',
        'composite_id',
        'video_id',
        'editor_id',
        'title',
        'description',
        'duration',
        'episode',
        'season',
        'view_count',
        'file_size',
        'mime_type',
        'poster_url',
        'date_released',
        'date_scanned',
        'date_uploaded',
    ];

    protected $casts = [
        'date_uploaded' => 'datetime',
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

    public function getDateReleasedFormattedAttribute() {
        return $this->attributes['date_released'] ? Carbon::parse($this->attributes['date_released'])->format('F d, Y') : null;
    }
}
