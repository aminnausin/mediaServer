<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Series extends Model {
    use HasFactory;

    protected $fillable = [
        'folder_id',
        'editor_id',
        'composite_id',
        'title',
        'description',
        'studio',
        'rating',
        'seasons',
        'episodes',
        'films',
        'date_start',
        'date_end',
        'thumbnail_url',
    ];

    protected $casts = [
        'primary_media_type' => MediaType::class,
    ];

    public function folder(): BelongsTo {
        return $this->belongsTo(Folder::class);
    }

    public function editor(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function folderTags(): HasMany {
        return $this->hasMany(FolderTag::class);
    }
}
