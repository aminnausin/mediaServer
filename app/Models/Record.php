<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'metadata_id',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function metadata() {
        return $this->belongsTo(Metadata::class);
    }
}
