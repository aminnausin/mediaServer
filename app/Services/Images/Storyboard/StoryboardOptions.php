<?php

namespace App\Services\Images\Storyboard;

use App\Models\Metadata;

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

        return new self(
            width: $cellWidth,
            height: $cellHeight,
            cols: 10,
            rows: 10,
            fps: 0.1,
            tile: true,
            sourceWidth: $sourceW,
            sourceHeight: $sourceH,
        );
    }

    private static function assertSupported(Metadata $metadata, string $filePath): void {
        // Unsupported codecs for hardware accelerated storyboard generation
        $unsupportedCodecs = ['vp9', 'vp8', 'av1'];

        if (empty($metadata->codec)) {
            throw new \RuntimeException('No codec detected for storyboard generation');
        }

        if (in_array($metadata->codec, $unsupportedCodecs)) {
            throw new \RuntimeException("Codec {$metadata->codec} not supported for storyboard generation");
        }

        if (empty($metadata->duration) || $metadata->duration < 10) {
            throw new \RuntimeException('Duration too short or missing for storyboard generation');
        }

        if (empty($metadata->resolution_width) || empty($metadata->resolution_height)) {
            throw new \RuntimeException('Resolution missing for storyboard generation');
        }

        if (str_starts_with($metadata->mime_type ?? '', 'audio/')) {
            throw new \RuntimeException('Audio files not supported for storyboard generation');
        }

        if (! file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }
    }
}
