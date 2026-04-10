<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait LogsDownloads {
    protected function logDownloadAttempt(string $status, array $data): void {
        $logContext = array_merge([
            'user_id' => Auth::id() ?? 'guest',
            'user_email' => Auth::user()?->email ?? 'guest',
            'user_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => $status,
            'timestamp' => now()->toDateTimeString(),
        ], $data);

        match ($status) {
            'success' => Log::info('Download completed', $logContext),
            'blocked' => Log::warning('Download blocked', $logContext),
            'failed' => Log::error('Download failed', $logContext),
            default => Log::debug('Download attempt', $logContext),
        };
    }
}
