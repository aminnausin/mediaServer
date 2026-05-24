<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Storyboard extends Model {
    /**
     * id               -> int8 (pk)
     * metadata_uuid    -> int8 (fk) (unique) (index) (onDelete=cascade)
     *
     * tile_rows        -> int2
     * tile_cols        -> int2
     * tile_width       -> int2
     * tile_height      -> int2
     * tile_count       -> int2
     *
     * interval_seconds -> int4
     *
     * created_at       -> timestamptz (nullable)
     * updated_at       -> timestamptz (nullable)
     * modified_at      -> timestamptz (nullable)
     *
     * command          -> text (nullable)
     * generation_time  -> float (nullable)
     */
    protected $casts = [
        'modified_at' => 'datetime',
    ];

    protected $fillable = [
        'metadata_uuid',
        'tile_rows',
        'tile_cols',
        'tile_width',
        'tile_height',
        'tile_count',
        'interval_seconds',
        'modified_at',
        'generation_time',
        'command',
    ];

    public function metadata(): BelongsTo {
        return $this->belongsTo(Metadata::class);
    }

    public static function getVisibleFields(): array {
        return [
            'tile_rows',
            'tile_cols',
            'tile_width',
            'tile_height',
            'tile_count',
            'interval_seconds',
        ];
    }
}
