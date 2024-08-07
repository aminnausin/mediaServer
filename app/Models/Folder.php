<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    public function videos(): HasMany{
        return $this->hasMany(Video::class);
    }

    public function category(): BelongsTo{
        return $this->belongsTo(Category::class);
    }
}
