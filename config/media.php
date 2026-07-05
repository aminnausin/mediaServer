<?php

return [
    'downloads' => [
        'max_size_mb' => env('MAX_DOWNLOAD_SIZE_MB', 1024 * 6),
    ],
    'image_downloads' => [
        'security' => [
            'allow_private_network_urls' => env('MEDIA_ALLOW_PRIVATE_URLS', false),
        ],
        'max_size_kb' => env('MAX_IMAGE_DOWNLOAD_SIZE_MB', 24) * 1024,
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
        'allowed_extensions' => ['jpeg', 'jpg', 'png', 'webp'],
        'max_size_kb' => env('MAX_IMAGE_UPLOAD_SIZE_MB', 24) * 1024,
    ],
];
