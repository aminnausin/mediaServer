<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Category extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'default_folder_id',
        'editor_id',
        'downloads_enabled',
        'downloads_require_auth',
        'storyboard_enabled',
        'is_private',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'downloads_enabled' => 'boolean',
        'downloads_require_auth' => 'boolean',
        'storyboard_enabled' => 'boolean',
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
        return $this->downloads_enabled;
    }

    public function storyboardEnabled(): bool {
        return $this->storyboard_enabled;
    }

    public function isVisibleTo(?User $user): bool {
        return ! $this->is_private || (bool) $user?->isAdmin();
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder {
        return $user?->isAdmin() ? $query : $query->where('is_private', false);
    }
}
