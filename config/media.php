<?php

return [
    'downloads' => [
        'max_size_mb' => env('MAX_DOWNLOAD_SIZE_MB', 1024 * 6),
    ],
    'storage' => [
        'prefix' => 'storage/media/',
    ],
    'storyboard' => [
        'default_interval_seconds' => 10,
        'daily_limit' => 200,
    ],
    'format_map' => [
        'mp4' => 'mp4',
        'm4a' => 'mp4',
        'mkv' => 'matroska',
        'mp3' => 'mp3',
        'ogg' => 'opus',
        'opus' => 'opus',
        'flac' => 'flac',
    ],
    'uploads' => [
        'allowed_extentions' => ['jpeg', 'jpg', 'png', 'webp'],
        'max_upload_size' => 1024 * 10,
    ],
];
