<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MediaController extends Controller {
    public function show(Request $request, $path) {
        Log::info('Request path: ' . $path);
        Log::info('Request full URL: ' . $request->fullUrl());
        if (!$request->hasValidSignature()) {
            Log::warning('Invalid signature for URL: ' . $request->fullUrl());
            abort(403);
        }

        $fullPath = storage_path("app/private/{$path}");
        if (file_exists($fullPath)) {
            $mimeType = Storage::mimeType("private/{$path}");

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
