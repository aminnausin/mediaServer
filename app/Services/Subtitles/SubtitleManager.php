<?php

namespace App\Services\Subtitles;

use App\Models\Metadata;
use App\Models\Subtitle;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SubtitleManager {
    public function resetSubtitleFiles(Metadata $metadata): void {
        $this->deleteSubtitleFiles($metadata);

        $metadata->subtitles()->update([
            'path' => null,
        ]);
    }

    public function purgeSubtitles(Metadata $metadata, bool $externalOnly = false): void {
        if ($externalOnly) {
            $this->deleteExternalSubtitleFiles($metadata);
        } else {
            // atomically delete all subtitle rows and files related to metadata
            $this->deleteSubtitleFiles($metadata);
            $metadata->subtitles()->delete();
        }
    }

    private function deleteExternalSubtitleFiles(Metadata $metadata): void {
        $metadata->subtitles()
            ->whereNotNull('external_path')
            ->each(function (Subtitle $subtitle) {
                if ($subtitle->path) {
                    Storage::disk('local')->delete($subtitle->path);
                }
            });

        $metadata->subtitles()->whereNotNull('external_path')->delete();
    }

    private function deleteSubtitleFiles(Metadata $metadata): void {
        $directory = SubtitlePath::buildDirectory($metadata->uuid);
        Log::info('Cleared Subtitle Directory ' . $metadata->uuid, ['uuid' => $metadata->uuid, 'title' => $metadata->title, 'dir' => $directory]);
        Storage::disk('local')->deleteDirectory($directory);
    }
}
