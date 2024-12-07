<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'media_content'];

    public function folders(): HasMany {
        return $this->hasMany(Folder::class);
    }

    protected static function boot() {
        parent::boot(); // Automatic withCount
        static::addGlobalScope('foldersCount', function ($builder) {
            $builder->withCount('folders');
        });
    }
}
