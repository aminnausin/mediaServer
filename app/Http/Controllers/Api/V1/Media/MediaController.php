<?php

namespace App\Http\Controllers\Api\V1\Media;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller {
    public function download(Video $video) {
        if (Auth::id() === null) {
            abort(403, 'Forbidden Action');
        }

        $path = substr($video->path, 7); // relative path from public disk
        abort_unless(Storage::disk('public')->exists($path), 404);

        $absolutePath = Storage::disk('public')->path($path);

        $maxSizeMB = config('media.downloads.max_size_mb', 4096);
        $maxSize = $maxSizeMB * 1024 * 1024;

        $fileSize = filesize($absolutePath);

        if ($fileSize > $maxSize) {
            $fileSizeMB = round($fileSize / (1024 * 1024), 2);
            abort(400, "File too large: {$fileSizeMB}MB. Maximum allowed download is {$maxSizeMB}MB.");
        }

        return response()->download($absolutePath, basename($absolutePath), [
            'Content-Length' => $fileSize,
        ]);
    }

    public function show(Request $request, $path) {
        Log::info('Request path: ' . $path);
        Log::info('Request full URL: ' . $request->fullUrl());
        if (! $request->hasValidSignature()) {
            Log::warning('Invalid signature for URL: ' . $request->fullUrl());
            abort(403);
        }

        $fullPath = storage_path("app/private/{$path}");
        if (file_exists($fullPath)) {
            $mimeType = Storage::mimeType("{$path}");

            return response()->file($fullPath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
            ]);
        } else {
            abort(404);
        }

        // $path = 'private/' . $path; // Adjust the path as needed
        // if (Storage::exists($path)) {
        //     // Prevent direct download by setting appropriate headers
        //     // return Storage::temporaryUrl(
        //     //     $path,
        //     //     now()->addHours(5)
        //     // );
        //     $fileContent = Storage::get($path);
        //     $mimeType = Storage::mimeType($path);

        //     return Response::make($fileContent, 200, [
        //         'Content-Type' => $mimeType,
        //         'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        //     ]);
        // } else {
        //     abort(403);
        // }
    }
}
