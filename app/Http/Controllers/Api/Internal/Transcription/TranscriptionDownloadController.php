<?php

namespace App\Http\Controllers\Api\Internal\Transcription;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Traits\LogsDownloads;
use Illuminate\Support\Facades\Storage;

class TranscriptionDownloadController extends Controller {
    use LogsDownloads;

    public function __invoke(Video $media) {
        $path = substr($media->path, 7); // relative path from public disk
        if (! Storage::disk('public')->exists($path)) {
            $this->logDownloadAttempt($media, 'blocked', ['reason' => 'File does not exist.']);
            abort(404);
        }

        $absolutePath = Storage::disk('public')->path($path);
        $fileSize = filesize($absolutePath);
        $fileSizeMB = round($fileSize / (1024 * 1024), 2);

        $this->logDownloadAttempt($media, 'success', ['reason' => 'Transcript generation', 'file_size_mb' => round($fileSizeMB)]);

        return response()->download($absolutePath, basename($absolutePath), [
            'Content-Length' => $fileSize,
        ]);
    }
}
