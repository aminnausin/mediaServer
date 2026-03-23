<?php

namespace App\Services\Subtitles;

use App\Models\Metadata;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SubtitleManager {
    public function reset(Metadata $metadata): void {
        $this->deleteSubtitleFiles($metadata);

        $metadata->subtitles()->update([
            'path' => null,
        ]);
    }

    private function deleteSubtitleFiles(Metadata $metadata): void {
        $directory = SubtitlePath::buildDirectory($metadata->uuid);
        Log::info('Cleared Subtitle Directory', ['uuid' => $metadata->uuid, 'title' => $metadata->title, 'dir' => $directory]);
        Storage::disk('local')->deleteDirectory($directory);
    }
}
