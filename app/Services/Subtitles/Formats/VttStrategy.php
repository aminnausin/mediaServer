<?php

namespace App\Services\Subtitles\Formats;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class VttStrategy implements SubtitleFormatStrategy {
    public function convert(string $inputPath, string $outputPath): void {
        $input = Storage::disk('local')->path($inputPath);
        $output = Storage::disk('local')->path($outputPath);

        $process = new Process([
            'ffmpeg',
            '-y',
            '-i',
            $input,
            '-c:s',
            'webvtt',
            $output,
        ]);

        $process->mustRun();

        $this->cleanVtt($output);
    }

    private function cleanVtt(string $path): void {
        $vtt = file_get_contents($path);

        if ($vtt === false) {
            return;
        }

        // normalise line endings
        $vtt = preg_replace("/\r\n?/", "\n", $vtt);

        if ($vtt === null) {
            return;
        }

        // separate vtt blocks
        $blocks = preg_split("/\n{2,}/", trim($vtt));

        if ($blocks === false) {
            return;
        }

        $cleaned = [];

        foreach ($blocks as $block) {
            $lines = explode("\n", $block);

            if (trim($lines[0]) === 'WEBVTT') {
                $cleaned[] = $block;

                continue;
            }

            $timestampIndex = null;
            foreach ($lines as $index => $line) {
                if (str_contains($line, ' --> ')) {
                    $timestampIndex = $index;
                    break;
                }
            }

            // preserve non-cue blocks
            if ($timestampIndex === null) {
                $cleaned[] = $block;

                continue;
            }

            $text = implode("\n", array_slice($lines, $timestampIndex + 1));
            $text = $this->stripOverrideTagsAndDrawings($text);
            $text = trim($text);

            if ($this->isAssDrawing($text) || $text === '') {
                continue;
            }

            // base off index + timestamp
            $cue = array_slice($lines, 0, $timestampIndex + 1);
            $cue[] = $text;
            $cleaned[] = implode("\n", $cue);
        }

        file_put_contents(
            $path,
            implode("\n\n", $cleaned) . "\n\n"
        );
    }

    /**
     * Removes ASS override tags (such as {\pos(...)}, {\an8}, {\i1}), and plain text between a {\pN}(N > 0){\p0}
     */
    private function stripOverrideTagsAndDrawings(string $text): string {
        $parts = preg_split('/(\{\\\\[^}]*\})/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return $text;
        }

        $result = '';
        $inDrawing = false;

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            if ($part[0] === '{') {
                if (preg_match('/\\\\p(\d+)/', $part, $m)) {
                    $inDrawing = ((int) $m[1]) > 0;
                }

                continue;
            }

            if (! $inDrawing) {
                $result .= $part;
            }
        }

        return $result;
    }

    private function isAssDrawing(string $text): bool {
        $text = trim($text);
        if ($text === '') {
            return false;
        }

        return preg_match('/^(?:[mnlbspc]|-?\d+(?:\.\d+)?|\s)+$/i', $text) === 1 && preg_match('/[mnlbspc]/i', $text) === 1 && preg_match('/\d/', $text) === 1;

        // less robust?

        if (! preg_match('/(?:^|\s)[mnlbspc](?=\s|$)/i', $text)) {
            return false;
        }

        return preg_match('/^(?:[mnlbspc]|-?\d+(?:\.\d+)?|\s)+$/i', $text) === 1;
    }
}
