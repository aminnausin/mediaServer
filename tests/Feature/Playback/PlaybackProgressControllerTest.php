<?php

namespace Tests\Feature\Playback;

use App\Models\Metadata;
use App\Models\PlaybackProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaybackProgressControllerTest extends TestCase {
    use RefreshDatabase;

    private User $user;

    private Metadata $metadata;

    protected function setUp(): void {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->metadata = Metadata::factory()->create(['duration' => 100]);
    }

    private function actingAsUser(): static {
        return $this->actingAs($this->user);
    }

    // -------------------------
    // show
    // -------------------------

    public function test_show_returns_zero_when_no_progress_exists(): void {
        $response = $this->actingAsUser()->getJson("/api/metadata/{$this->metadata->id}/progress");

        $response->assertOk()->assertJson([
            'progress_offset' => 0,
            'progress_percentage' => 0,
        ]);
    }

    public function test_show_returns_existing_progress(): void {
        PlaybackProgress::factory()->create([
            'user_id' => $this->user->id,
            'metadata_id' => $this->metadata->id,
            'progress_offset' => 40,
            'progress_percentage' => 40,
        ]);

        $response = $this->actingAsUser()->getJson("/api/metadata/{$this->metadata->id}/progress");

        $response->assertOk()->assertJson([
            'progress_offset' => 40,
            'progress_percentage' => 40,
        ]);
    }

    public function test_show_only_returns_authenticated_users_progress(): void {
        $otherUser = User::factory()->create();

        PlaybackProgress::factory()->create([
            'user_id' => $otherUser->id,
            'metadata_id' => $this->metadata->id,
            'progress_offset' => 60,
            'progress_percentage' => 60,
        ]);

        $response = $this->actingAsUser()->getJson("/api/metadata/{$this->metadata->id}/progress");

        $response->assertOk()->assertJson([
            'progress_offset' => 0,
            'progress_percentage' => 0,
        ]);
    }

    public function test_show_requires_authentication(): void {
        $this->getJson("/api/metadata/{$this->metadata->id}/progress")
            ->assertForbidden();
    }

    // -------------------------
    // upsert
    // -------------------------

    public function test_upsert_creates_new_progress_record(): void {
        $this->actingAsUser()->putJson("/api/metadata/{$this->metadata->id}/progress", [
            'progress_offset' => 40,
        ])->assertNoContent();

        $this->assertDatabaseHas('playback_progress', [
            'user_id' => $this->user->id,
            'metadata_id' => $this->metadata->id,
            'progress_offset' => 40,
            'progress_percentage' => 40,
        ]);
    }

    public function test_upsert_updates_existing_progress_record(): void {
        PlaybackProgress::factory()->create([
            'user_id' => $this->user->id,
            'metadata_id' => $this->metadata->id,
            'progress_offset' => 40,
            'progress_percentage' => 40,
        ]);

        $this->actingAsUser()->putJson("/api/metadata/{$this->metadata->id}/progress", [
            'progress_offset' => 60,
        ])->assertNoContent();

        $this->assertDatabaseHas('playback_progress', [
            'user_id' => $this->user->id,
            'metadata_id' => $this->metadata->id,
            'progress_offset' => 60,
            'progress_percentage' => 60,
        ]);

        $this->assertDatabaseCount('playback_progress', 1);
    }

    public function test_upsert_clamps_offset_to_duration(): void {
        $this->actingAsUser()->putJson("/api/metadata/{$this->metadata->id}/progress", [
            'progress_offset' => 999,
        ])->assertNoContent();

        $this->assertDatabaseHas('playback_progress', [
            'user_id' => $this->user->id,
            'metadata_id' => $this->metadata->id,
            'progress_offset' => 100,
        ]);
    }

    public function test_upsert_marks_completion_at_threshold(): void {
        $this->actingAsUser()->putJson("/api/metadata/{$this->metadata->id}/progress", [
            'progress_offset' => 96,
        ])->assertNoContent();

        $progress = PlaybackProgress::where('user_id', $this->user->id)
            ->where('metadata_id', $this->metadata->id)
            ->first();

        $this->assertEquals(100, $progress->progress_offset);
        $this->assertEquals(100, $progress->progress_percentage);
        $this->assertEquals(1, $progress->completion_count);
        $this->assertNotNull($progress->last_completed_at);
    }

    public function test_upsert_does_not_increment_completion_count_if_already_completed(): void {
        PlaybackProgress::factory()->create([
            'user_id' => $this->user->id,
            'metadata_id' => $this->metadata->id,
            'progress_offset' => 100,
            'progress_percentage' => 100,
            'completion_count' => 1,
            'last_completed_at' => now()->subDay(),
        ]);

        $this->actingAsUser()->putJson("/api/metadata/{$this->metadata->id}/progress", [
            'progress_offset' => 98,
        ])->assertNoContent();

        $this->assertDatabaseHas('playback_progress', [
            'user_id' => $this->user->id,
            'metadata_id' => $this->metadata->id,
            'completion_count' => 1,
        ]);
    }

    public function test_upsert_increments_completion_count_on_rewatch(): void {
        PlaybackProgress::factory()->create([
            'user_id' => $this->user->id,
            'metadata_id' => $this->metadata->id,
            'progress_offset' => 100,
            'progress_percentage' => 100,
            'completion_count' => 1,
        ]);

        // progress gets set to below the threshold and then completes again
        $this->actingAsUser()->putJson("/api/metadata/{$this->metadata->id}/progress", [
            'progress_offset' => 10,
        ])->assertNoContent();

        $this->actingAsUser()->putJson("/api/metadata/{$this->metadata->id}/progress", [
            'progress_offset' => 97,
        ])->assertNoContent();

        $this->assertDatabaseHas('playback_progress', [
            'completion_count' => 2,
        ]);
    }

    public function test_upsert_requires_authentication(): void {
        $this->putJson("/api/metadata/{$this->metadata->id}/progress", [
            'progress_offset' => 40,
        ])->assertForbidden();
    }

    public function test_upsert_requires_progress_offset(): void {
        $this->actingAsUser()->putJson("/api/metadata/{$this->metadata->id}/progress", [])
            ->assertUnprocessable();
    }

    public function test_upsert_requires_progress_offset_to_be_integer(): void {
        $this->actingAsUser()->putJson("/api/metadata/{$this->metadata->id}/progress", [
            'progress_offset' => 'not-an-integer',
        ])->assertUnprocessable();
    }
}
