<?php

namespace App\Services\Subtitles\Formats;

interface SubtitleFormatStrategy {
    public function convert(string $inputPath, string $outputPath): void;
}
