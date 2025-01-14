<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Playback extends Model {
    use HasFactory;

    protected $fillable = [
        'metadata_id',
        'progress',
        'count',
    ];

    public function metadata(): BelongsTo {
        return $this->belongsTo(Metadata::class);
    }
}
