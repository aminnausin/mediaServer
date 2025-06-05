<?php

namespace Tests\Feature;

use App\Models\Metadata;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetadataControllerTest extends TestCase {
    use RefreshDatabase;

    protected function authenticateUser() {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_show_returns_metadata_resource() {
        $this->authenticateUser();

        $metadata = Metadata::factory()->create();
        $this->getJson("/api/metadata/{$metadata->id}")
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $metadata->id]);
    }

    public function test_store_creates_metadata_or_updates_existing() {
        $this->authenticateUser();

        $video = Video::factory()->create();
        $payload = [
            'video_id' => $video->id,
            'title' => 'Test Title',
            'description' => 'Artist - Track',
            'duration' => 120,
            'video_tags' => [],
            'deleted_tags' => [],
        ];

        $this->postJson('/api/metadata', $payload)
            ->assertStatus(200)
            ->assertJsonPath('data.id', "{$video->id}");
    }

    public function test_update_edits_metadata_and_returns_updated_video() {
        $this->authenticateUser();

        $metadata = Metadata::factory()->create(['video_id' => Video::factory()]);
        $payload = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'duration' => 999,
            'video_tags' => [],
            'deleted_tags' => [],
        ];
        $this->patchJson("/api/metadata/{$metadata->id}", $payload)
            ->assertStatus(200)
            ->assertJsonPath('data.metadata.title', 'Updated Title');
    }

    public function test_update_lyrics_works_and_ignores_track_field() {
        $this->authenticateUser();

        $metadata = Metadata::factory()->create(['video_id' => Video::factory()]);
        $payload = [
            'lyrics' => 'Synced lyrics here',
            'album' => 'Test Album',
            'artist' => 'Test Artist',
            'track' => 'Should be ignored',
        ];

        $this->patchJson("/api/metadata/{$metadata->id}/lyrics", $payload)
            ->assertStatus(200)
            ->assertJsonFragment(['album' => 'Test Album']);
    }

    public function test_update_lyrics_returns_error_without_associated_video() {
        $this->authenticateUser();

        $metadata = Metadata::factory()->create();
        $payload = [
            'lyrics' => 'Synced lyrics here',
        ];

        $this->patchJson("/api/metadata/{$metadata->id}/lyrics", $payload)
            ->assertStatus(500)
            ->assertJsonPath('message', 'Unable to edit song. Error: Song does not exist');
    }
}
