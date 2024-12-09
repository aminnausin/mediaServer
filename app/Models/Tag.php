<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'creator_id'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    public function metadata()
    {
        return $this->belongsTo(Metadata::class);
    }
}
