<?php

namespace App\Services\FFmpeg;

readonly class ExtractAudioOptions {
    public function __construct(
        public int $stream = 0,
        public int $channels = 1,
        public int $sampleRate = 16000,
        public string $codec = 'flac',
        public string $format = 'flac',
    ) {}
}
