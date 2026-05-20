<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
     * interval_ms      -> int4
     *
     * created_at       -> timestamptz (nullable)
     * updated_at       -> timestamptz (nullable)
     * modified_at      -> timestamptz (nullable)
     */
    protected $casts = [
        'modified_at' => 'datetime',
    ];
}
