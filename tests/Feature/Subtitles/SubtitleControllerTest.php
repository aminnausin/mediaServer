<?php

namespace Tests\Feature\Subtitles;

use App\Models\Metadata;
use App\Models\Subtitle;
use App\Services\Subtitles\SubtitleExtractor;
use App\Services\Subtitles\SubtitleFormatter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubtitleControllerTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();

        $this->mock(SubtitleExtractor::class)->shouldIgnoreMissing();
        $this->mock(SubtitleFormatter::class)->shouldIgnoreMissing();
    }

    public function test_returns_subtitle_file() {
        $metadata = $this->makeMetadataAndSubtitlePair();
        $response = $this->get("/data/subtitles/{$metadata->uuid}/2.vtt");

        $response->assertOk();
        $this->assertStringContainsString('text/vtt', $response->headers->get('Content-Type'));
    }

    public function test_default_format_is_used_when_missing() {
        $metadata = $this->makeMetadataAndSubtitlePair();
        $response = $this->get("/data/subtitles/{$metadata->uuid}/2");

        $response->assertOk();
        $this->assertStringContainsString('text/vtt', $response->headers->get('Content-Type'));
    }

    public function test_returns_404_for_missing_subtitle() {
        $metadata = Metadata::factory()->create();
        $response = $this->get("/data/subtitles/{$metadata->uuid}/999.vtt");

        $response->assertNotFound();
    }

    public function test_invalid_uuid_returns_404() {
        $response = $this->get('/data/subtitles/not-a-uuid/2.vtt');

        $response->assertNotFound();
    }

    private function makeMetadataAndSubtitlePair(): Metadata {
        $metadata = Metadata::factory()->create();
        Subtitle::factory()->create([
            'metadata_uuid' => $metadata->uuid,
            'track_id' => 2,
        ]);

        Storage::fake('local');
        Storage::disk('local')->put(
            'metadata/media/' . substr($metadata->uuid, 0, 2) . "/{$metadata->uuid}/subtitles/2.vtt",
            "WEBVTT\n\n00:09.443 --> 00:10.844\nARE YOU READY, KIDS?\n\n00:10.911 --> 00:12.746\nKids:\nAYE AYE, CAPTAIN!"
        );

        return $metadata;
    }
}
