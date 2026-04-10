<?php

namespace App\Traits;

use App\Models\Video;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait LogsDownloads {
    protected function logDownloadAttempt(Video $file, string $status, array $data): void {
        $logContext = array_merge([
            'file' => [
                'id' => $file->id,
                'file_path' => $file->path,
            ],
            'user' => [
                'id' => Auth::id() ?? 'guest',
                'email' => Auth::user()?->email ?? 'guest',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
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
