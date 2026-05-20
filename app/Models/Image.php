<?php

namespace App\Models;

use App\Enums\ImageSource;
use App\Enums\ImageType;
use Illuminate\Database\Eloquent\Model;

class Image extends Model {
    /**
     * id               -> int8 (pk)
     * metadata_uuid    -> int8 (fk) (index) (onDelete=cascade)
     *
     * user_id          -> int8 (fk) (nullable) (onDelete=setNull)
     *
     * image_type       -> int2 (enum) (composite index with metadata_uuid, is_primary)
     * source           -> int2 (enum)
     * is_primary       -> boolean (default=false)
     *
     * width            -> int4 (nullable)
     * height           -> int4 (nullable)
     * blurhash         -> varchar(64)
     *
     * path             -> text (nullable)
     * source_url       -> text (nullable)
     *
     * created_at       -> timestamptz (nullable)
     * updated_at       -> timestamptz (nullable)
     */
    protected $casts = [
        'image_type' => ImageType::class,
        'source' => ImageSource::class,
        'is_primary' => 'boolean',
    ];
}
