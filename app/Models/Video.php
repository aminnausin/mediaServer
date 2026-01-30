<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Video extends Model {
    use HasFactory;

    public $timestamps = false;

    /**
     * id                   -> int8 (pk) (index)
     * folder_id            -> int8 (fk) (not indexed ???)
     *
     * name                 -> varchar(255)
     * path                 -> varchar(255) (index)
     * date                 -> varchar(255)
     *
     * uuid                 -> uuid (index) (nullable) (un-maintained)
     * created_at           -> timestamp (nullable)
     *
     * title                -> varchar(255) (nullable) (legacy - remove)
     * description          -> varchar(255) (nullable) (legacy - remove)
     * duration             -> int4 (nullable) (legacy - remove)
     * episode              -> int4 (nullable) (legacy - remove)
     * season               -> int4 (nullable) (legacy - remove)
     * view_count           -> int4 (nullable) (legacy - remove)
     */
    protected $fillable = [
        'title',
        'description',
        'duration',
        'episode',
        'season',
        'view_count',
        'uuid',
    ];

    public function folder(): BelongsTo {
        return $this->belongsTo(Folder::class);
    }

    public function metadata(): HasOne {
        return $this->hasOne(Metadata::class);
    }
}
