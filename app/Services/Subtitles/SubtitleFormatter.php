<?php

namespace App\Services\Subtitles;

use App\Services\Subtitles\Formats\VttStrategy;
use Illuminate\Support\Facades\Log;
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
        $conversionStart = microtime(true);
        $timings = [];

        try {
            $inputCheckStart = microtime(true);
            if (! Storage::disk('local')->exists($input)) {
                throw new \RuntimeException("Input file not found: {$input}");
            }
            $timings['input_exists_check'] = microtime(true) - $inputCheckStart;

            // if ($input === $output) {
            //     return Storage::disk('local')->path($input);
            // }

            $sameFileStart = microtime(true);
            $isSameFile = $input === $output;
            $timings['same_file_check'] = microtime(true) - $sameFileStart;

            if ($isSameFile) {
                $timings['total_conversion'] = microtime(true) - $conversionStart;

                Log::info('Subtitle conversion skipped (same file)', [
                    'input' => $input,
                    'output' => $output,
                    'format' => $outputFormat,
                    'timings_ms' => array_map(fn ($t) => round($t * 1000, 2), $timings),
                ]);

                return Storage::disk('local')->path($input);
            }

            $strategy = $this->matchStrategy($outputFormat);

            $actualConversionStart = microtime(true);
            $strategy->convert($input, $output);
            $timings['actual_conversion'] = microtime(true) - $actualConversionStart;

            $outputCheckStart = microtime(true);
            if (! Storage::disk('local')->exists($output)) {
                throw new \RuntimeException("Conversion failed: {$output} not created");
            }
            $timings['output_verification'] = microtime(true) - $outputCheckStart;

            $timings['total_conversion'] = microtime(true) - $conversionStart;

            Log::info('Subtitle conversion completed', [
                'input' => $input,
                'output' => $output,
                'format' => $outputFormat,
                'strategy' => get_class($strategy),
                'timings_ms' => array_map(fn ($t) => round($t * 1000, 2), $timings),
            ]);

            return Storage::disk('local')->path($output);
        } catch (\Throwable $th) {
            Log::error('Subtitle conversion failed.', [
                'input' => $input,
                'output' => $output,
                'outputFormat' => $outputFormat,
                'error' => $th->getMessage(),
            ]);
            throw $th;
        }
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
