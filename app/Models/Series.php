<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class series extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'studio',
        'seasons',
        'episodes',
        'films',
        'date_start',
        'date_end',
        'thumbnail_url',
        'editor_id'
    ];

    public function folder(): BelongsTo {
        return $this->belongsTo(Folder::class);
    }

    public function editor(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
