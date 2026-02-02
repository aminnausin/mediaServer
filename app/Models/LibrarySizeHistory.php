<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibrarySizeHistory extends Model {
    public function library(): BelongsTo {
        return $this->belongsTo(Category::class);
    }
}
