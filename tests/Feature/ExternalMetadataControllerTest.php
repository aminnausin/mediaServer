<?php

namespace Tests\Feature;

use App\Models\Metadata;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExternalMetadataControllerTest extends TestCase {
    use RefreshDatabase;

    public function test_import_lyrics_returns_expected_payload() {
        $metadata = Metadata::factory()->create([
            'title' => 'Test Title',
            'description' => 'Test Artist - Something',
            'album' => 'Test Album',
            'duration' => 120,
        ]);

        Http::fake([
            'https://lrclib.net/api/get*' => Http::response(['syncedLyrics' => 'lyrics text'], 200),
        ]);

        $this->getJson("/api/metadata/{$metadata->id}/lyrics/import")
            ->assertStatus(200)
            ->assertJsonStructure([
                'lrclib' => ['syncedLyrics'],
                'payload' => ['track_name', 'artist_name', 'album_name', 'duration'],
            ]);
    }

    public function test_search_lyrics_with_custom_query() {
        $metadata = Metadata::factory()->create();

        Http::fake([
            'https://lrclib.net/api/search*' => Http::response(['lrclib' => [['id' => 123]], 'payload' => []], 200),
        ]);

        $this->getJson("/api/metadata/{$metadata->id}/lyrics/search?artist=Test+Artist")
            ->assertStatus(200)
            ->assertJsonPath('lrclib.lrclib.0.id', 123);
    }

    public function test_import_fails_for_missing_metadata() {
        $this->getJson('/api/metadata/99999/lyrics/import')
            ->assertStatus(404);
    }
}
