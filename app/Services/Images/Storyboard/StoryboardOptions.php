<?php

namespace App\Services\Images\Storyboard;

use App\Exceptions\StoryboardNotSupportedException;
use App\Jobs\VerifyFiles;
use App\Models\Metadata;
use App\Services\Ffmpeg\FFprobeService;

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
        public int $tileCount,
        public bool $skipFrameNokey,
    ) {}

    public static function fromMetadata(Metadata $metadata): self {
        self::assertSupported($metadata);

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
            $metadata->duration < 10 => 2, // every 500ms
            $metadata->duration < 30 => 1, // every second
            $metadata->duration < 120 => 0.5, // every other second
            default => 1 / $intervalSeconds, // every (default=10) seconds
        };

        $expectedFrames = ceil($metadata->duration * $fps);
        $cols = min(10, $expectedFrames);
        $rows = min(10, max(1, ceil($expectedFrames / $cols)));

        $skipFrameNokey = match (true) {
            $fps <= 0.1 => true,
            $metadata->duration > 120 => true,
            default => FFprobeService::countKeyframes(VerifyFiles::getAbsoluteMediaPath($metadata->video)) >= $expectedFrames,
        };

        return new self(
            width: $cellWidth,
            height: $cellHeight,
            cols: $cols,
            rows: $rows,
            fps: $fps,
            tile: true,
            sourceWidth: $sourceW,
            sourceHeight: $sourceH,
            duration: $metadata->duration,
            skipFrameNokey: $skipFrameNokey,
            tileCount: $expectedFrames
        );
    }

    private static function assertSupported(Metadata $metadata): void {
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
    }
}
