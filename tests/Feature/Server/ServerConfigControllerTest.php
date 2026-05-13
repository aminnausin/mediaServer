<?php

namespace Tests\Feature\Server;

use App\Models\ServerConfig;
use App\Models\User;
use App\Services\Server\QueueControlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServerConfigControllerTest extends TestCase {
    use RefreshDatabase;

    private function adminUser(): User {
        // admin when id === 1
        return User::factory()->create(['id' => 1]);
    }

    private function regularUser(): User {
        return User::factory()->create(['id' => 2]);
    }

    private function seedConfig(array $overrides = []): ServerConfig {
        $defaults = [
            'key' => 'some_key',
            'value' => 'some_value',
            'default_value' => 'default_value',
            'type' => 'string',
            'group' => 'scanner',
        ];

        return ServerConfig::forceCreate(array_merge($defaults, $overrides));
    }

    // GET | PATCH /config/*

    public function test_update_requires_authentication(): void {
        $this->getJson('/api/config')->assertUnauthorized();
        $this->patchJson('/api/config/scanner', [])->assertUnauthorized();
        $this->patchJson('/api/config/performance', [])->assertUnauthorized();
        $this->patchJson('/api/config/storage', [])->assertUnauthorized();
        $this->patchJson('/api/config/media', [])->assertUnauthorized();
    }

    public function test_update_rejects_non_admin(): void {
        $this->actingAs($this->regularUser());
        $this->patchJson('/api/config/scanner', [])->assertForbidden();
        $this->patchJson('/api/config/performance', [])->assertForbidden();
        $this->patchJson('/api/config/storage', [])->assertForbidden();
        $this->patchJson('/api/config/media', [])->assertForbidden();
    }

    public function test_update_validates_required_fields(): void {
        $this->actingAs($this->adminUser());

        $this->patchJson('/api/config/scanner', [])->assertUnprocessable()->assertJsonValidationErrors([
            'uuid_embed',
            'uuid_write_cache',
            'attachments_extract',
            'art_extract',
            'thumbnails_generate',
        ]);

        $this->patchJson('/api/config/performance', [])->assertUnprocessable()->assertJsonValidationErrors(['max_scan_workers', 'max_event_workers']);
        $this->patchJson('/api/config/media', [])->assertUnprocessable()->assertJsonValidationErrors(['supported_extensions']);
    }

    public function test_update_rejects_invalid_input(): void {
        $this->actingAs($this->adminUser());

        $this->patchJson('/api/config/scanner', ['uuid_embed' => 'not-a-bool'])->assertUnprocessable();
        $this->patchJson('/api/config/performance', ['max_scan_workers' => '#'])->assertUnprocessable();
        $this->patchJson('/api/config/storage', ['metadata_path' => 4])->assertUnprocessable();
        $this->patchJson('/api/config/media', ['supported_extensions' => ['M', 'toolongext', 'MP3', 'mp-3']])->assertUnprocessable()->assertJsonValidationErrors(['supported_extensions.0', 'supported_extensions.1', 'supported_extensions.3']);
    }

    public function test_update_persists_values_and_returns_204(): void {
        $scannerPayload = [
            'uuid_embed' => true,
            'uuid_write_cache' => false,
            'attachments_extract' => true,
            'art_extract' => false,
            'thumbnails_generate' => true,
        ];

        $path = str_replace('\\', '/', sys_get_temp_dir());

        $this->actingAs($this->adminUser());

        $this->patchJson('/api/config/scanner', $scannerPayload)->assertNoContent();
        $this->patchJson('/api/config/storage', ['cache_path' => $path, 'metadata_path' => $path])->assertNoContent();
        $this->patchJson('/api/config/media', ['supported_extensions' => ['mp3', 'flac', 'wav']])->assertNoContent();

        $this->assertDatabaseHas('server_configs', ['key' => 'uuid_embed',       'value' => '1']);
        $this->assertDatabaseHas('server_configs', ['key' => 'uuid_write_cache', 'value' => '0']);
        $this->assertDatabaseHas('server_configs', ['key' => 'cache_path',    'value' => $path]);
        $this->assertDatabaseHas('server_configs', ['key' => 'metadata_path', 'value' => $path]);
        $this->assertDatabaseHas('server_configs', ['key' => 'supported_extensions', 'value' => json_encode(['mp3', 'flac', 'wav'])]);
    }

    // GET /config

    public function test_index_returns_grouped_values_and_defaults(): void {
        ServerConfig::truncate(); // clear default seeded values
        $this->seedConfig(['key' => 'uuid_embed', 'value' => '1', 'default_value' => '1', 'type' => 'bool', 'group' => 'scanner']);

        $response = $this->actingAs($this->adminUser())->getJson('/api/config');

        $response->assertOk();
        $this->assertTrue($response->json('values.scanner.uuid_embed'));
        $this->assertTrue($response->json('defaults.scanner.uuid_embed'));
    }

    // PATCH /config/performance

    public function test_update_performance_persists_values_restarts_queue_and_returns_204(): void {
        $mock = $this->mock(QueueControlService::class);
        $mock->expects('restart')->once();

        $this->actingAs($this->adminUser())->patchJson('/api/config/performance', ['max_scan_workers' => 4, 'max_event_workers' => 2])->assertNoContent();

        $this->assertDatabaseHas('server_configs', ['key' => 'max_scan_workers',  'value' => '4']);
        $this->assertDatabaseHas('server_configs', ['key' => 'max_event_workers', 'value' => '2']);
    }

    public function test_update_performance_does_not_restart_queue_on_validation_failure(): void {
        $mock = $this->mock(QueueControlService::class);
        $mock->expects('restart')->never();

        $this->actingAs($this->adminUser())->patchJson('/api/config/performance', ['max_scan_workers' => 999])->assertUnprocessable();
    }

    // PATCH /config/storage

    public function test_update_storage_rejects_invalid_paths(): void {
        $this->actingAs($this->adminUser());

        $this->patchJson('/api/config/storage', ['cache_path' => '/path/with/../traversal'])->assertUnprocessable();
        $this->patchJson('/api/config/storage', ['metadata_path' => '/nonexistent/path'])->assertUnprocessable();
    }

    public function test_update_storage_normalises_paths(): void {
        $cachePath = Storage::disk('local')->path('cache');

        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        $this->actingAs($this->adminUser())->patchJson('/api/config/storage', ['cache_path' => 'storage\\cache/'])->assertNoContent();

        $this->assertDatabaseHas('server_configs', ['key' => 'cache_path', 'value' => 'storage/cache']);
    }

    public function test_update_storage_blocks_root_storage_folder(): void {
        $this->actingAs($this->adminUser())->patchJson('/api/config/storage', ['cache_path' => 'storage'])->assertUnprocessable();
    }
}
