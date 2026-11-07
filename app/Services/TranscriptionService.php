<?php

namespace App\Services;

use App\Models\Metadata;
use App\Models\Subtitle;
use Illuminate\Support\Facades\Storage;

class TranscriptionService {
    public function saveTranscript(Metadata $metadata, string $vtt, ?string $language): Subtitle {
        $subtitle = $metadata->subtitles()->firstOrNew([
            'source_key' => 'generated',
            'track_id' => -1,
        ]);

        $oldPath = $subtitle->path;

        $subtitle->fill([
            'language' => $language,
            'title' => 'Auto-generated Transcript',
            'codec' => 'vtt',
            'format' => 'vtt',
            'is_default' => $subtitle->exists ? $subtitle->is_default : ! $metadata->subtitles()->exists(),
            'is_forced' => false,
            'source_key' => 'generated',
        ]);

        $path = $subtitle->getFilePath('vtt', $language);
        $subtitle->path = $path;
        $subtitle->save();

        Storage::disk('local')->put($path, $vtt);

        if ($oldPath && $oldPath !== $path) {
            Storage::disk('local')->delete($oldPath);
        }

        return $subtitle;
    }
}
