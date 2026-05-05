<?php

namespace App\Services\Server;

use App\Models\ServerConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServerConfigService {
    public function all(): Collection {
        return Cache::rememberForever('server_config:all', function () {
            return ServerConfig::all()->keyBy('key')->map(fn ($record) => [
                'value' => json_decode($record->value, true) ?? $record->value,
                'default_value' => json_decode($record->default_value, true) ?? $record->default_value,
                'type' => $record->type,
                'group' => $record->group,
            ]);
        });
    }

    public function get(string $key, mixed $default = null): mixed {
        return $this->all()->get($key)['value'] ?? $default ?? $this->getDefault($key);
    }

    public function getDefault(string $key): mixed {
        return $this->all()->get($key)['default_value'] ?? null;
    }

    public function set(string $key, mixed $value): void {
        ServerConfig::update(['key' => $key], ['value' => json_encode($value ?? $this->getDefault($key))]);
        Cache::forget('server_config:all');
    }

    public function setMany(array $values): void {
        DB::transaction(function () use ($values) {
            foreach ($values as $key => $value) {
                ServerConfig::where('key', $key)->update(['value' => json_encode($value ?? $this->getDefault($key))]);
            }
        });

        Cache::forget('server_config:all');
    }
}
