<?php

namespace App\Models;

use App\Casts\ServerConfigValue;
use Illuminate\Database\Eloquent\Model;

class ServerConfig extends Model {
    /**
     * key                  -> string (pk) (unique) (index)
     * value                -> text
     * default_value        -> text
     * type                 -> enum ('string', 'boolean', 'integer', 'array', 'float')
     * group                -> enum ('scanner', 'metadata', 'media', 'performance', 'storage')
     *
     * created_at           -> timestamptz (nullable)
     * updated_at           -> timestamptz (nullable)
     */
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'value',
        'default_value',
        'type',
        'group',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'value' => ServerConfigValue::class,
        'default_value' => ServerConfigValue::class,
    ];
}
