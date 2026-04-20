<?php

namespace Tests\Unit\Services\Auth;

use App\Models\Metadata;
use App\Models\PlaybackProgress;
use App\Models\Record;
use App\Models\User;
use App\Services\Auth\GuestMergeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestMergeServiceTest extends TestCase {
    use RefreshDatabase;

    private GuestMergeService $service;

    private User $user;

    private Metadata $metadata;

    private string $guestToken;

    protected function setUp(): void {
        parent::setUp();
        $this->service = new GuestMergeService;
        $this->user = User::factory()->create();
        $this->metadata = Metadata::factory()->create(['duration' => 100]);
        $this->guestToken = fake()->uuid();
    }

    private function createGuestProgress(array $overrides = []): PlaybackProgress {
        return PlaybackProgress::factory()->create([
            'user_id' => null,
            'guest_token' => $this->guestToken,
            'metadata_id' => $this->metadata->id,
            ...$overrides,
        ]);
    }

    private function createUserProgress(array $overrides = []): PlaybackProgress {
        return PlaybackProgress::factory()->create([
            'user_id' => $this->user->id,
            'guest_token' => null,
            'metadata_id' => $this->metadata->id,
            ...$overrides,
        ]);
    }

    // -------------------------
    // Basic merge behaviour
    // -------------------------

    public function test_merge_does_nothing_with_null_token(): void {
        $this->createGuestProgress(['progress_offset' => 50]);

        $this->service->merge($this->user, null);

        $this->assertDatabaseHas('playback_progress', ['guest_token' => $this->guestToken]);
    }

    public function test_merge_transfers_guest_row_to_user_when_no_conflict(): void {
        $this->createGuestProgress(['progress_offset' => 50, 'progress_percentage' => 50]);

        $this->service->merge($this->user, $this->guestToken);

        $this->assertDatabaseHas('playback_progress', [
            'user_id' => $this->user->id,
            'metadata_id' => $this->metadata->id,
            'progress_offset' => 50,
            'progress_percentage' => 50,
            'guest_token' => null,
        ]);
    }

    public function test_merge_deletes_guest_rows_after_transfer(): void {
        $this->createGuestProgress();

        $this->service->merge($this->user, $this->guestToken);

        $this->assertDatabaseMissing('playback_progress', ['guest_token' => $this->guestToken]);
    }

    public function test_merge_handles_multiple_guest_rows_across_different_media(): void {
        $metadata2 = Metadata::factory()->create(['duration' => 200]);

        PlaybackProgress::factory()->create([
            'user_id' => null,
            'guest_token' => $this->guestToken,
            'metadata_id' => $this->metadata->id,
            'progress_offset' => 40,
        ]);

        PlaybackProgress::factory()->create([
            'user_id' => null,
            'guest_token' => $this->guestToken,
            'metadata_id' => $metadata2->id,
            'progress_offset' => 80,
        ]);

        $this->service->merge($this->user, $this->guestToken);

        $this->assertDatabaseCount('playback_progress', 2);
        $this->assertDatabaseMissing('playback_progress', ['guest_token' => $this->guestToken]);
    }

    // -------------------------
    // Conflict strategies
    // -------------------------

    public function test_latest_strategy_takes_guest_value_when_guest_row_is_newer(): void {
        $this->createUserProgress([
            'progress_offset' => 80,
            'updated_at' => now()->subHour(),
        ]);

        $this->createGuestProgress([
            'progress_offset' => 40,
            'updated_at' => now(),
        ]);

        $this->service->merge($this->user, $this->guestToken);

        $this->assertDatabaseHas('playback_progress', [
            'user_id' => $this->user->id,
            'progress_offset' => 40, // guest value is more recent
        ]);
    }

    public function test_latest_strategy_keeps_user_value_when_user_row_is_newer(): void {
        $this->createUserProgress([
            'progress_offset' => 80,
            'updated_at' => now(),
        ]);

        $this->createGuestProgress([
            'progress_offset' => 40,
            'updated_at' => now()->subHour(),
        ]);

        $this->service->merge($this->user, $this->guestToken);

        $this->assertDatabaseHas('playback_progress', [
            'user_id' => $this->user->id,
            'progress_offset' => 80, // user value is more recent
        ]);
    }

    public function test_sum_strategy_adds_completion_counts(): void {
        $this->createUserProgress(['completion_count' => 3]);
        $this->createGuestProgress(['completion_count' => 2]);

        $this->service->merge($this->user, $this->guestToken);

        $this->assertDatabaseHas('playback_progress', [
            'user_id' => $this->user->id,
            'completion_count' => 5,
        ]);
    }

    public function test_greatest_strategy_takes_most_recent_updated_at(): void {
        $older = now()->subDay();
        $newer = now();

        $this->createUserProgress(['updated_at' => $older]);
        $this->createGuestProgress(['updated_at' => $newer]);

        $this->service->merge($this->user, $this->guestToken);

        $progress = PlaybackProgress::where('user_id', $this->user->id)
            ->where('metadata_id', $this->metadata->id)
            ->first();

        $this->assertTrue($progress->updated_at->gte($newer->subSecond()));
    }

    public function test_keep_existing_strategy_preserves_user_record_id(): void {
        $record = Record::factory()->create([
            'user_id' => $this->user->id,
            'metadata_id' => $this->metadata->id,
        ]);

        // The guest system isn't implemented for records so this is a fake record with a userId instead of a guestToken
        $record2 = Record::factory()->create([
            'user_id' => $this->user->id,
            'metadata_id' => $this->metadata->id,
        ]);
        $this->createUserProgress(['record_id' => $record->id]);
        $this->createGuestProgress(['record_id' => $record2->id]);

        $this->service->merge($this->user, $this->guestToken);

        $this->assertDatabaseHas('playback_progress', [
            'user_id' => $this->user->id,
            'record_id' => $record->id,
        ]);
    }

    public function test_keep_existing_strategy_takes_guest_record_id_when_user_has_none(): void {
        $record = Record::factory()->create([
            'user_id' => $this->user->id,
            'metadata_id' => $this->metadata->id,
        ]);

        $this->createUserProgress(['record_id' => null]);
        $this->createGuestProgress(['record_id' => $record->id]);

        $this->service->merge($this->user, $this->guestToken);

        $this->assertDatabaseHas('playback_progress', [
            'user_id' => $this->user->id,
            'record_id' => $record->id,
        ]);
    }

    // -------------------------
    // Edge cases
    // -------------------------

    public function test_merge_with_no_guest_rows_does_nothing(): void {
        $this->createUserProgress(['progress_offset' => 50]);

        $this->service->merge($this->user, $this->guestToken);

        $this->assertDatabaseHas('playback_progress', [
            'user_id' => $this->user->id,
            'progress_offset' => 50,
        ]);
    }

    public function test_merge_is_wrapped_in_transaction(): void {
        // Force second table to fail by using fake table name in config
        // This should rollback all merge transactions
        $this->createGuestProgress(['progress_offset' => 50]);

        $brokenService = new class extends GuestMergeService {
            protected array $tables = [
                'playback_progress' => [
                    'conflict_key' => '(user_id, metadata_id) WHERE user_id IS NOT NULL',
                    'columns' => ['progress_offset' => 'latest', 'updated_at' => 'greatest'],
                ],
                'nonexistent_table' => [
                    'conflict_key' => '(user_id, metadata_id) WHERE user_id IS NOT NULL',
                    'columns' => ['updated_at' => 'greatest'],
                ],
            ];
        };

        try {
            $brokenService->merge($this->user, $this->guestToken);
        } catch (\Throwable) {
            // transaction rolled back
            $this->assertDatabaseHas('playback_progress', ['guest_token' => $this->guestToken]);
        }
    }
}
