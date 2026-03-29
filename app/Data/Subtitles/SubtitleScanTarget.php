<?php

namespace App\Data\Subtitles;

class SubtitleScanTarget {
    public function __construct(
        public readonly string $uuid,
        public readonly bool $fileUpdated,
    ) {
    }
}
