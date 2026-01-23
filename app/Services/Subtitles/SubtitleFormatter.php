<?php

namespace App\Services\Subtitles;

use App\Services\Subtitles\Formats\VttStrategy;
use Illuminate\Support\Facades\Storage;

class SubtitleFormatter {
    /**
     * Convert a local subtitle file to another format
     *
     * @param  string  $input  Relative local disk path of the input file
     * @param  string  $output  Relative local disk path of the output file
     * @param  string  $outputFormat  Output file format
     * @return string Converted file path
     */
    public function convert(string $input, string $output, string $outputFormat): string {
        if (! Storage::disk('local')->exists($input)) {
            throw new \RuntimeException("Input file not found: {$input}");
        }

        $strategy = $this->matchStrategy($outputFormat);

        $strategy->convert($input, $output);

        if (! Storage::disk('local')->exists($output)) {
            throw new \RuntimeException("Conversion failed: {$output} not created");
        }

        return Storage::disk('local')->path($output);
    }

    /**
     * Matches a formatting strategy given a file format
     * (Only supports vtt)
     *
     * @param  string  $outputFormat  File format
     */
    protected function matchStrategy(string $outputFormat) {
        return match ($outputFormat) {
            'vtt' => new VttStrategy,
            default => throw new \InvalidArgumentException("Unsupported format $outputFormat"),
        };
    }
}
