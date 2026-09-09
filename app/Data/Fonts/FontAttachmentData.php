<?php

namespace App\Data\Fonts;

final readonly class FontAttachmentData {
    public function __construct(
        public int $streamIndex,
        public string $fileName,
        public ?string $codec,
        public string $mimeType,
    ) {}
}
