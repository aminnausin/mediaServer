<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeriesSizeHistory extends Model {
    protected $fillable = [
        'series_id',
        'total_bytes',
        'file_count',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function series(): BelongsTo {
        return $this->belongsTo(Series::class);
    }
}
