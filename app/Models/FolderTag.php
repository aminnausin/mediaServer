<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FolderTag extends Model {
    protected $fillable = [
        'series_id',
        'tag_id',
    ];

    public function series() {
        return $this->belongsTo(Series::class);
    }

    public function tag() {
        return $this->belongsTo(Tag::class);
    }
}
