<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Category extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'default_folder_id', 'editor_id'];

    public function folders(): HasMany {
        return $this->hasMany(Folder::class);
    }

    public function videos(): HasManyThrough {
        return $this->hasManyThrough(Video::class, Folder::class);
    }

    public function editor(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    // protected static function boot() {
    // parent::boot(); // Automatic withCount
    // static::addGlobalScope('foldersCount', function ($builder) {
    //     $builder->withCount('folders');
    // });
    // }
}
