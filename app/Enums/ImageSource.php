<?php

namespace App\Enums;

enum ImageSource: string {
    case GENERATED = 'generated';
    case UPLOADED = 'uploaded';
    case API = 'api';
    case URL = 'url'; // external url, not downloaded, makes no sense
    case DOWNLOADED = 'downloaded';
    case EMBEDDED = 'embedded';
    case LEGACY = 'legacy';

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
