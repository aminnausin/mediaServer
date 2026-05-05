<?php

return [
    'downloads' => [
        'max_size_mb' => env('MAX_DOWNLOAD_SIZE_MB', 1024 * 6),
    ],
    'storage' => [
        'prefix' => 'storage/media/',
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
];
