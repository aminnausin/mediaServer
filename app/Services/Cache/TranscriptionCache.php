<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Storage;

class TranscriptionCache {
    public const DIR = 'cache/transcripts';

    public static function audio(int $taskId, string $uuid): string {
        return Storage::disk('local')->path(self::DIR . "/{$taskId}-{$uuid}.flac");
    }

    public static function resultKey(int $taskId, string $uuid): string {
        return self::DIR . "/{$taskId}-{$uuid}.json";
    }
}
