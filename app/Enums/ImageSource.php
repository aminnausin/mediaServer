<?php

namespace App\Enums;

enum ImageSource: int {
    case GENERATED = 0;
    case UPLOADED = 1;
    case API = 2;
    case URL = 3; // external url, not downloaded, makes no sense
    case DOWNLOADED = 4;
    case EMBEDDED = 5;
    case LEGACY = 6;

    public function label(): string {
        return strtolower($this->name);
    }

    public function isUserOwned(): bool {
        return in_array($this, self::USER_OWNED_SOURCES);
    }

    // User-owned sources that auto-processing should never overwrite as primary
    private const USER_OWNED_SOURCES = [
        ImageSource::UPLOADED,
        ImageSource::URL,
    ];

    // Auto sources that should be replaced if the file updates
    private const AUTO_SOURCES = [
        ImageSource::GENERATED,
        ImageSource::EMBEDDED,
        ImageSource::DOWNLOADED,
    ];
}
