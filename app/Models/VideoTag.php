<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoTag extends Model {
    use HasFactory;

    protected $fillable = [
        'metadata_id',
        'tag_id',
    ];

    public function metadata() {
        return $this->belongsTo(Metadata::class);
    }

    public function tag() {
        return $this->belongsTo(Tag::class);
    }
}
