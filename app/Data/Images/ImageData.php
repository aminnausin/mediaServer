<?php

namespace App\Data\Images;

use App\Enums\ImageSource;
use App\Enums\ImageType;
use App\Enums\ImageVariant;

readonly class ImageData {
    public function __construct(
        public string $absolutePath,
        public string $relativePath,
        public ImageType $type,
        public ImageSource $source,
        public string $format,
        public ?string $sourceUrl = null,
        public ?ImageVariant $variant = null,
        public ?int $userId = null,
    ) {}
}
