<?php

namespace App\Services\External;

use App\Models\Metadata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LrcLibService {
    public function importLyrics(Metadata $metadata): array {
        $url = 'https://lrclib.net/api/get';

        $query = array_filter([
            'track_name' => $metadata->title ?? $metadata->name,
            'artist_name' => $metadata->artist ?? explode(' - ', $metadata->description)[0],
            'album_name' => $metadata->album,
            'duration' => $metadata->duration,
        ], fn($value) => $value !== null && $value !== '');

        $response = Http::get($url, $query);

        Log::info("Lrclib Get", [
            'url' => $url . '?' . http_build_query($query),
            'payload' => $query,
            'response' => $response->json()
        ]);

        return ['lrclib' => $response->json(), 'payload' => $query];
    }

    public function searchLyrics(Metadata $metadata, Request $request): array {
        $url = 'https://lrclib.net/api/search';

        // This should not use the defaults so that the user can edit the search to blanks
        $query = array_filter([
            'track_name' => $request->query('track'), // ?? $metadata->title,
            'artist_name' => $request->query('artist'), // ?? $metadata->artist ?? explode(' - ', $metadata->description)[0],
            'album_name' => $request->query('album'), // ?? $metadata->album,
        ], fn($value) => $value !== null && $value !== '');

        $response = Http::get($url, $query);

        Log::info("Lrclib Search", [
            'url' => $url . '?' . http_build_query($query),
            'payload' => $query,
            'response' => $response->json()
        ]);

        return ['lrclib' => $response->json(), 'payload' => $query];
    }
}
