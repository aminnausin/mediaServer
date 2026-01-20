<?php

namespace App\Services\Subtitles;

use App\Enums\SubtitleSource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

// Per Metadata Row
class SubtitleScanner {
    public function scanEmbeddedSubtitles(string $uuid, array $fileMetaData): array {
        $subtitleStreams = $this->filterSubtitleStreams($fileMetaData);

        return count($subtitleStreams) > 0
            ? $this->buildSubtitleTransactions($uuid, $subtitleStreams)
            : [];
    }

    public function filterSubtitleStreams(array $fileMetaData): array {
        return array_filter(
            $fileMetaData['streams'] ?? [],
            fn ($stream) => ($stream['codec_type'] ?? null) === 'subtitle'
        );
    }

    public function buildSubtitleTransactions(string $uuid, array $streams): array {
        $transactions = [];
        foreach ($streams as $stream) {
            if (! isset($stream['index']) || ! isset($stream['codec_name'])) {
                Log::warning('Skipping invalid subtitle stream', [
                    'uuid' => $uuid,
                    'stream' => $stream,
                ]);

                continue;
            }

            $transaction = [
                'metadata_uuid' => $uuid,
                'track_id' => $stream['index'], // is 0 if external
                'language' => $stream['tags']['language'] ?? 'und',
                'codec' => $stream['codec_name'],
                'is_default' => ($stream['disposition']['default'] ?? 0) === 1,
                'is_forced' => ($stream['disposition']['forced'] ?? 0) === 1,
                'external_path' => null,
                'source_key' => SubtitleSource::EMBEDDED->makeKey($stream['index']),
            ];

            if (isset($stream['external_path'])) {
                $transaction['external_path'] = $stream['external_path'];
                $transaction['source_key'] = SubtitleSource::EXTERNAL->makeKey($stream['external_path']);
            }

            $transactions[] = $transaction;
        }

        return $transactions;
    }

    /**
     * @param  string  $directoryPath  the relative directory of the given metadata row /library/folder
     * @return array a list of arrays that resemble ffprobe output for each matching subtitle file
     */
    public function findExternalSubtitlesInDirectory(string $directoryPath): array {
        $extensions = ['srt', 'vtt', 'ass', 'ssa', 'sub'];
        $externalStreams = [];

        if (config('app.env') === 'local') {
            dump("SCANNING FOR SUBTITLES IN $directoryPath");
        }

        foreach ($extensions as $ext) {
            // match pattern basename.*.ext (ex/ movie.*.srt)
            $pattern = "{$directoryPath}/*.{$ext}";
            $files = glob($pattern);

            foreach ($files as $file) {
                $filename = basename($file);
                $parts = explode('.', $filename);

                // Parse: movie.en.forced.srt
                // parts: [movie, en, forced, srt]
                $media_filename = array_shift($parts); // remove related media file name
                $extension = array_pop($parts); // remove extension

                if (empty($parts)) {
                    continue;
                }

                $language = $parts[0];
                $flags = array_slice($parts, 1);

                $isForced = in_array('forced', $flags);
                $isDefault = in_array('default', $flags);

                // Build ffprobe output
                $externalStreams[] = [
                    'media_filename' => $media_filename,
                    'index' => 0,
                    'codec_type' => 'subtitle',
                    'codec_name' => $this->getCodecFromExtension($extension),
                    'tags' => [
                        'language' => $language,
                    ],
                    'disposition' => [
                        'forced' => $isForced ? 1 : 0,
                        'default' => $isDefault ? 1 : 0,
                    ],
                    'external_path' => str_replace(str_replace('\\', '/', Storage::disk('public')->path('')), '', $file), // store relative location
                ];
            }
        }

        return $externalStreams;
    }

    private function getCodecFromExtension(string $ext): string {
        return match ($ext) {
            'vtt' => 'webvtt',
            'srt' => 'subrip',
            'ass' => 'ass',
            'ssa' => 'ssa',
            'sub' => 'dvd_subtitle',
            default => 'unknown',
        };
    }
}
