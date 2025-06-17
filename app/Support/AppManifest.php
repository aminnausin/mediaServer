<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class AppManifest {
    public static function info(): array {
        $path = storage_path('app/manifest.json');

        if (! File::exists($path)) {
            Artisan::call('app:manifest');

            return ['version' => 'unknown', 'commit' => null];
        }

        $data = json_decode(File::get($path), true);

        return [
            'version' => $data['version'] ?? 'unknown',
            'commit' => $data['commit'] ?? null,
        ];
    }

    public static function string(): string {
        $info = self::info();

        return "{$info['version']} (#{$info['commit']})";
    }
}
