<?php

namespace Tests\Unit;

use App\Models\Metadata;
use App\Services\External\LrcLibService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class LrcLibServiceTest extends TestCase {
    public function test_import_lyrics_makes_correct_http_request() {
        Http::fake([
            'https://lrclib.net/api/get*' => Http::response(['syncedLyrics' => 'Lyrics'], 200),
        ]);

        $metadata = Metadata::factory()->make([
            'title' => 'Test Song',
            'description' => 'Test Artist - Something',
            'album' => null,
            'duration' => 123,
        ]);

        $service = new LrcLibService;
        $response = $service->importLyrics($metadata);

        Http::assertSent(function ($request) {
            return Str::startsWith($request->url(), 'https://lrclib.net/api/get') &&
                $request['track_name'] === 'Test Song' &&
                $request['artist_name'] === 'Test Artist' &&
                $request['duration'] === 123 && empty($request['album']);
        });

        $this->assertEquals('Lyrics', $response['lrclib']['syncedLyrics']);
    }

    public function test_search_lyrics_uses_query_overrides() {
        Http::fake([
            'https://lrclib.net/api/search*' => Http::response(['results' => []], 200),
        ]);

        $metadata = Metadata::factory()->make([
            'description' => 'Wrong Artist',
        ]);

        $request = request()->merge([
            'track' => 'Overridden Song',
            'artist' => 'Correct Artist',
        ]);

        $service = new LrcLibService;
        $response = $service->searchLyrics($metadata, $request);

        Http::assertSent(function ($request) {
            return $request['track_name'] === 'Overridden Song' &&
                $request['artist_name'] === 'Correct Artist';
        });

        $this->assertArrayHasKey('lrclib', $response);
    }
}
