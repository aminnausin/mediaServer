<?php

return [
    'downloads' => [
        'max_size_mb' => env('MAX_DOWNLOAD_SIZE_MB', 1024 * 6),
    ],
    'storage' => [
        'prefix' => 'storage/media/',
    ],
];
