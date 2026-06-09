<?php

namespace App\Models;

use App\Enums\ImageSource;
use App\Enums\ImageType;
use App\Enums\ImageVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model {
    /**
     * id               -> int8 (pk)
     *
     * imageable_type   -> varchar(255)
     * imageable_id     -> uuid (fk) (index) (composite index with imageable_type)
     *
     * user_id          -> int8 (fk) (nullable) (onDelete=setNull)
     *
     * image_type       -> string (enum) (composite index with imageable_type, imageable_id, replaced_at)
     * image_source     -> string (enum)
     * image_variant    -> string (enum) (nullable)
     *
     * width            -> int4 (nullable)
     * height           -> int4 (nullable)
     * size             -> uint8 (default=0)
     * blur_hash        -> varchar(64) (nullable)
     *
     * format           -> varchar(10) (nullable)
     * path             -> text (nullable)
     * source_url       -> text (nullable)
     *
     * replaced_at      -> timestamptz (nullable)
     *
     * created_at       -> timestamptz (nullable)
     * updated_at       -> timestamptz (nullable)
     */
    protected $casts = [
        'image_type' => ImageType::class,
        'image_source' => ImageSource::class,
        'image_variant' => ImageVariant::class,
    ];

    protected $fillable = [
        'imageable_type',
        'imageable_id',

        'image_type',
        'image_source',
        'image_variant',
        'width',
        'height',
        'size',
        'blur_hash',

        'format',
        'path',
        'source_url',

        'replaced_at',
    ];

    public function imageable(): MorphTo {
        return $this->morphTo();
    }

    public function filename(): string {
        $variant = $this->image_variant ? "_{$this->image_variant->value}" : '';
        $ext = $this->format ?? 'webp';

        return $this->image_type->value . "{$variant}.{$ext}";
    }
}
