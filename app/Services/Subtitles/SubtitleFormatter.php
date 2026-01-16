<?php

namespace App\Services\Subtitles;

use App\Services\Subtitles\Formats\VttStrategy;
use Illuminate\Support\Facades\Storage;

class SubtitleFormatter {
    public function convert(string $input, string $output, string $outputFormat): string {
        if (! Storage::disk('local')->exists($input)) {
            throw new \RuntimeException("Input file not found: {$input}");
        }

        $strategy = $this->matchStrategy($outputFormat);

        $strategy->convert($input, $output);

        if (! Storage::disk('local')->exists($output)) {
            throw new \RuntimeException("Conversion failed: {$output} not created");
        }

        return $output;
    }

    protected function matchStrategy(string $outputFormat) {
        return match ($outputFormat) {
            'vtt' => new VttStrategy,
            default => throw new \InvalidArgumentException('Unsupported format'),
        };
    }
}
