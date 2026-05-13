<?php

namespace Tests\Unit\Requests\Server;

use App\Http\Requests\Server\UpdateStorageConfigRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UpdateStorageConfigRequestTest extends TestCase {
    #[DataProvider('invalidPathProvider')]
    public function test_rejects_invalid_paths(string $path, string $expectedError): void {
        $validator = Validator::make(
            ['cache_path' => $path],
            (new UpdateStorageConfigRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey($expectedError, $validator->errors()->toArray());
    }

    public static function invalidPathProvider(): array {
        return [
            'path traversal' => ['/var/www/../etc/passwd', 'cache_path'],
            'null byte' => ["/tmp/path\0evil", 'cache_path'],
            'nonexistent' => ['/does/not/exist', 'cache_path'],
        ];
    }

    public function test_accepts_valid_paths(): void {
        $validator = Validator::make(
            ['cache_path' => storage_path()],
            (new UpdateStorageConfigRequest)->rules()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_accepts_null_paths(): void {
        $validator = Validator::make(
            ['cache_path' => null],
            (new UpdateStorageConfigRequest)->rules()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_rejects_storage_root(): void {
        $validator = Validator::make(
            ['cache_path' => 'storage'],
            (new UpdateStorageConfigRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cache_path', $validator->errors()->toArray());
    }
}
