<?php

namespace App\Models;

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
}
