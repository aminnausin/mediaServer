<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Metadata extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'duration',
        'episode',
        'season',
        'view_count',
        'tags',
        'date_released'
    ];
    
    public function video(): BelongsTo {
        return $this->belongsTo(Video::class);
    }

    public function editor(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function playback(): HasMany{
        return $this->hasMany(Playback::class);
    }
}
