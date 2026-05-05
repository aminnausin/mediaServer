<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServerConfig extends Model {
    /**
     * key                  -> string (pk) (unique) (index)
     * value                -> text
     * default_value        -> text
     * type                 -> enum ('string', 'boolean', 'integer', 'array', 'float')
     * group                -> enum ('scanning', 'metadata', 'media', 'performance', 'storage')
     *
     * created_at           -> timestamptz (nullable)
     * updated_at           -> timestamptz (nullable)
     */
    protected $fillable = [
        'value',
        'default_value',
        'type',
        'group',
        'created_at',
        'updated_at',
    ];
}
