<?php

namespace Tests\Unit\Services\Server;

use App\Models\ServerConfig;
use App\Services\Server\ServerConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ServerConfigServiceTest extends TestCase {
    use RefreshDatabase;

    private ServerConfigService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->service = new ServerConfigService;
        ServerConfig::truncate(); // undo default config seeding
        Cache::flush();
    }

    private function makeConfig(array $overrides = []): ServerConfig {
        $defaults = [
            'key' => 'some_key',
            'value' => 'some_value',
            'default_value' => 'default_value',
            'type' => 'string',
            'group' => 'scanner',
        ];

        return ServerConfig::forceCreate(array_merge($defaults, $overrides));
    }

    // all()

    public function test_all_returns_keyed_collection(): void {
        $this->makeConfig(['key' => 'foo']);
        $this->makeConfig(['key' => 'bar']);

        $result = $this->service->all();

        $this->assertArrayHasKey('foo', $result->toArray());
        $this->assertArrayHasKey('bar', $result->toArray());
    }

    public function test_all_caches_result(): void {
        $this->makeConfig(['key' => 'foo']);

        $this->service->all();

        $this->assertTrue(Cache::has('server_config:all'));
    }

    // get()

    public function test_get_returns_cast_value_for_existing_key(): void {
        $this->makeConfig(['key' => 'max_workers', 'value' => '4', 'type' => 'integer']);

        $result = $this->service->get('max_workers');

        $this->assertSame(4, $result);
    }

    public function test_get_returns_passed_default_when_value_is_null(): void {
        $this->makeConfig(['key' => 'some_key', 'value' => null, 'default_value' => 'db_default', 'type' => 'string']);

        $result = $this->service->get('some_key', 'caller_default');

        // $default param has priority over default_value when value is null
        $this->assertSame('caller_default', $result);
    }

    public function test_get_falls_back_to_db_default_when_no_caller_default_and_value_is_null(): void {
        $this->makeConfig(['key' => 'some_key', 'value' => null, 'default_value' => 'db_default', 'type' => 'string']);

        $result = $this->service->get('some_key');

        $this->assertSame('db_default', $result);
    }

    public function test_get_returns_null_for_unknown_key(): void {
        $result = $this->service->get('nonexistent_key');

        $this->assertNull($result);
    }

    public function test_get_returns_caller_default_for_unknown_key(): void {
        $result = $this->service->get('nonexistent_key', 'fallback');

        $this->assertSame('fallback', $result);
    }

    public function test_get_casts_boolean_correctly(): void {
        $this->makeConfig(['key' => 'flag', 'value' => '1', 'type' => 'bool']);

        $this->assertTrue($this->service->get('flag'));
    }

    public function test_get_casts_array_correctly(): void {
        $this->makeConfig(['key' => 'extensions', 'value' => '["mp3","flac"]', 'type' => 'array']);

        $this->assertSame(['mp3', 'flac'], $this->service->get('extensions'));
    }

    // getDefault()

    public function test_get_default_returns_cast_default_value(): void {
        $this->makeConfig(['key' => 'workers', 'default_value' => '8', 'type' => 'integer']);

        $this->assertSame(8, $this->service->getDefault('workers'));
    }

    public function test_get_default_returns_null_for_unknown_key(): void {
        $this->assertNull($this->service->getDefault('nonexistent'));
    }

    public function test_get_default_returns_null_when_default_value_is_null(): void {
        $this->makeConfig(['key' => 'some_key', 'default_value' => null, 'type' => 'string']);

        $this->assertNull($this->service->getDefault('some_key'));
    }

    // set()

    public function test_set_updates_value_in_database(): void {
        $this->makeConfig(['key' => 'uuid_embed', 'value' => '0', 'type' => 'bool']);

        $this->service->set('uuid_embed', true);

        $this->assertDatabaseHas('server_configs', ['key' => 'uuid_embed', 'value' => '1']);
    }

    public function test_set_busts_cache(): void {
        $this->makeConfig(['key' => 'uuid_embed', 'value' => '0', 'type' => 'bool']);

        $this->service->all();
        $this->assertTrue(Cache::has('server_config:all'));

        $this->service->set('uuid_embed', true);

        $this->assertFalse(Cache::has('server_config:all'));
    }

    public function test_set_falls_back_to_default_when_given_null(): void {
        $this->makeConfig(['key' => 'some_key', 'value' => 'old', 'default_value' => 'default', 'type' => 'string']);

        $this->service->set('some_key', null);

        $this->assertDatabaseHas('server_configs', ['key' => 'some_key', 'value' => 'default']);
    }

    // setMany()

    public function test_set_many_updates_multiple_values(): void {
        $this->makeConfig(['key' => 'max_scan_workers',  'value' => '10', 'type' => 'integer', 'group' => 'performance']);
        $this->makeConfig(['key' => 'max_event_workers', 'value' => '5',  'type' => 'integer', 'group' => 'performance']);

        $this->service->setMany([
            'max_scan_workers' => 4,
            'max_event_workers' => 2,
        ]);

        $this->assertDatabaseHas('server_configs', ['key' => 'max_scan_workers',  'value' => '4']);
        $this->assertDatabaseHas('server_configs', ['key' => 'max_event_workers', 'value' => '2']);
    }

    public function test_set_many_busts_cache(): void {
        $this->makeConfig(['key' => 'max_scan_workers', 'value' => '10', 'type' => 'integer', 'group' => 'performance']);

        $this->service->all();
        $this->assertTrue(Cache::has('server_config:all'));

        $this->service->setMany(['max_scan_workers' => 4]);

        $this->assertFalse(Cache::has('server_config:all'));
    }

    public function test_set_many_falls_back_to_default_for_null_values(): void {
        $this->makeConfig(['key' => 'some_key', 'value' => 'old', 'default_value' => 'default', 'type' => 'string']);

        $this->service->setMany(['some_key' => null]);

        $this->assertDatabaseHas('server_configs', ['key' => 'some_key', 'value' => 'default']);
    }

    public function test_fresh_get_after_set_returns_new_value(): void {
        $this->makeConfig(['key' => 'uuid_embed', 'value' => '0', 'type' => 'bool']);

        $this->assertFalse($this->service->get('uuid_embed'));

        $this->service->set('uuid_embed', true);

        $this->assertTrue($this->service->get('uuid_embed'));
    }

    // clearCache()

    public function test_clear_cache_removes_cache(): void {
        $this->makeConfig(['key' => 'foo']);
        $this->service->all();
        $this->assertTrue(Cache::has('server_config:all'));

        $this->service->clearCache();

        $this->assertFalse(Cache::has('server_config:all'));
    }

    public function test_clear_cache_returns_true_when_cache_exists(): void {
        $this->makeConfig(['key' => 'foo']);
        $this->service->all();

        $result = $this->service->clearCache();

        $this->assertTrue($result);
    }

    public function test_clear_cache_returns_false_when_cache_does_not_exist(): void {
        $result = $this->service->clearCache();

        $this->assertFalse($result);
    }
}
