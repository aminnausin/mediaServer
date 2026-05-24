<?php

namespace App\Services\Images\Storyboard;

use App\Exceptions\StoryboardNotSupportedException;
use App\Models\Metadata;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class StoryboardOptions {
    public function __construct(
        public int $width,
        public int $height,
        public int $cols,
        public int $rows,
        public float $fps,
        public bool $tile,
        public int $sourceWidth,
        public int $sourceHeight,
        public int $duration,
    ) {}

    public static function fromMetadata(Metadata $metadata, string $filePath): self {
        self::assertSupported($metadata, $filePath);

        $sourceW = $metadata->resolution_width ?? 1920;
        $sourceH = $metadata->resolution_height ?? 1080;

        $maxTileSize = 320;

        $cellWidth = $maxTileSize;
        $cellHeight = $maxTileSize;

        if ($sourceW > $sourceH) {
            $cellWidth = $maxTileSize;
            $cellHeight = (int) max(2, floor(($maxTileSize * $sourceH / $sourceW) / 2) * 2);
        } elseif ($sourceH > $sourceW) {
            $cellHeight = $maxTileSize;
            $cellWidth = (int) max(2, floor(($maxTileSize * $sourceW / $sourceH) / 2) * 2);
        }

        $intervalSeconds = max(1, config('media.storyboard.default_interval_seconds', 10)); // min configured value is every second

        $fps = match (true) {
            $metadata->duration < 10 => 10, // every 100ms
            $metadata->duration < 30 => 1.0, // every second
            $metadata->duration < 120 => 0.5, // every other second
            default => 1 / $intervalSeconds, // every (default=10) seconds
        };

        return new self(
            width: $cellWidth,
            height: $cellHeight,
            cols: 10,
            rows: 10,
            fps: $fps,
            tile: true,
            sourceWidth: $sourceW,
            sourceHeight: $sourceH,
            duration: $metadata->duration,
        );
    }

    private static function assertSupported(Metadata $metadata, string $filePath): void {
        // Unsupported codecs for hardware accelerated storyboard generation
        $unsupportedCodecs = ['vp9', 'vp8'];

        if (empty($metadata->codec)) {
            throw new StoryboardNotSupportedException('No codec detected for storyboard generation');
        }

        if (in_array($metadata->codec, $unsupportedCodecs)) {
            throw new StoryboardNotSupportedException("Codec {$metadata->codec} not supported for storyboard generation");
        }

        if (empty($metadata->duration) || $metadata->duration <= 0) {
            throw new StoryboardNotSupportedException('Duration too short or missing for storyboard generation');
        }

        if (empty($metadata->resolution_width) || empty($metadata->resolution_height)) {
            throw new StoryboardNotSupportedException('Resolution missing for storyboard generation');
        }

        if (str_starts_with($metadata->mime_type ?? '', 'audio/')) {
            throw new StoryboardNotSupportedException('Audio files not supported for storyboard generation');
        }

        if (! file_exists($filePath)) {
            throw new FileNotFoundException("File not found: {$filePath}");
        }
    }
}
