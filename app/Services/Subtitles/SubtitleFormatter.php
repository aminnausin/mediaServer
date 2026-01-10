<?php

namespace App\Services\Subtitles;

use App\Services\Subtitles\Formats\VttStrategy;
use Illuminate\Support\Facades\Storage;

class SubtitleFormatter {
    public function convert(string $input, string $output, string $format): string {
        if (! Storage::disk('local')->exists($input)) {
            throw new \RuntimeException("Input file not found: {$input}");
        }

        $strategy = match ($format) {
            'vtt' => new VttStrategy,
            default => throw new \InvalidArgumentException('Unsupported format'),
        };

        $strategy->convert($input, $output);

        if (! Storage::disk('local')->exists($output)) {
            throw new \RuntimeException("Conversion failed: {$output} not created");
        }

        return $output;
    }
}
