<?php

namespace App\Data\Fonts;

final readonly class ExtractedFontData {
    public function __construct(
        public string $metadataUuid,
        public string $fileName,
        public ?string $codec,
        public string $mimeType,
        public int $size,
        public string $path,
        public string $hash,
    ) {}
}
