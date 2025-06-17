<?php

namespace App\Services\External;

use App\Models\Metadata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LrcLibService {
    public function importLyrics(Metadata $metadata): array {
        $query = array_filter([
            'track_name' => $metadata->title,
            'artist_name' => explode(' - ', $metadata->description)[0],
            'album_name' => $metadata->album,
            'duration' => $metadata->duration,
        ], fn ($value) => $value !== null && $value !== '');

        $response = Http::get('https://lrclib.net/api/get', $query);

        return ['lrclib' => $response->json(), 'payload' => $query];
    }

    public function searchLyrics(Metadata $metadata, Request $request): array {
        $query = array_filter([
            'track_name' => $request->query('track') ?? $metadata->title,
            'artist_name' => $request->query('artist') ?? explode(' - ', $metadata->description)[0],
            'album_name' => $request->query('album'),
        ], fn ($value) => $value !== null && $value !== '');

        $response = Http::get('https://lrclib.net/api/search', $query);

        return ['lrclib' => $response->json(), 'payload' => $query];
    }
}
