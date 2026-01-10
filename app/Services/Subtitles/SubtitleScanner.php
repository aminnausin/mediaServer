<?php

namespace App\Services\Subtitles;

use Illuminate\Support\Facades\Log;

// Per Metadata Row
class SubtitleScanner {
    public function extractSubtitleStreams(array $fileMetadata): array {
        return array_filter(
            $fileMetadata['streams'] ?? [],
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

            $transactions[] = [
                'metadata_uuid' => $uuid,
                'track_id' => $stream['index'],
                'language' => $stream['tags']['language'] ?? 'und',
                'codec' => $stream['codec_name'],
            ];
        }

        return $transactions;
    }
}
