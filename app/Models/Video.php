<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'duration',
        'episode',
        'season',
        'view_count'
    ];
    public function folder(): BelongsTo{
        return $this->belongsTo(Folder::class);
    }
}
