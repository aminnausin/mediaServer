<?php

namespace App\Services\Image;

use App\Jobs\VerifyFiles;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Downloader {
    public function __construct() {
    }

    // TODO: Eventually use this service with DI in verifyFiles and verifyFolders to download thumbnail urls ... currently using static function in verifyFolders
    public function importExternalImageAsURL($url, $compositePath) {
        try {
            $response = Http::get($url);
            if ($response->successful()) {
                dump('Getting thumbnail');
                $imageContent = $response->body();
                $path = 'thumbnails/' . $compositePath . '.webp';
                Storage::disk('public')->put($path, $imageContent);

                return VerifyFiles::getPathUrl($path);
            }
        } catch (\Throwable $th) {
            // throw $th;
            Log::warning('Unable to download thumbnail image from ' . $url . ' : ' . $th->getMessage());
        }

        return false;
    }
}
