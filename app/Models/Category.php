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

    protected $fillable = ['name', 'default_folder_id', 'editor_id', 'allow_downloads', 'require_login_for_downloads'];

    protected $casts = [
        'allow_downloads' => 'boolean',
        'require_login_for_downloads' => 'boolean',
    ];

    public function folders(): HasMany {
        return $this->hasMany(Folder::class);
    }

    public function videos(): HasManyThrough {
        return $this->hasManyThrough(Video::class, Folder::class);
    }

    public function editor(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function sizeHistory(): HasMany {
        return $this->hasMany(LibrarySizeHistory::class);
    }

    public function downloadsEnabled(): bool {
        return $this->allow_downloads;
    }
}
