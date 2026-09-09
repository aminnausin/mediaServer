<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Font extends Model {
    /**
     * id                   -> int8 (pk) (index)
     * metadata_uuid        -> uuid (fk) (index) (onDelete=setNull)
     *
     * file_name            -> text
     * codec                -> varchar(32) (nullable)
     * mime_type            -> varchar(255)
     * size                 -> uint8 (default=0)
     *
     * path                 -> text
     *
     * hash                 -> varchar(64) (nullable)
     *
     * created_at           -> timestamptz (nullable)
     * updated_at           -> timestamptz (nullable)
     */
    protected $fillable = [
        'metadata_uuid',
        'file_name',
        'codec',
        'mime_type',
        'path',
        'hash',
    ];

    protected $table = 'font_attachments';

    public function metadata(): BelongsTo {
        return $this->belongsTo(Metadata::class, 'uuid', 'metadata_uuid');
    }

    public static function getVisibleFields(): array {
        return ['id', 'metadata_uuid', 'path'];
    }
}
