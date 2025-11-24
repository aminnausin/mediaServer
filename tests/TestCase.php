<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
    protected function setUp(): void {
        parent::setUp();

        // 🚨 Safety check to prevent test from wiping real data
        if (env('DB_DATABASE') === config('app.name')) {
            throw new InvalidTestEnvironmentException('🚫 Tests are running on your development database! Check DB_DATABASE in .env or phpunit.xml');
        }
    }
}

class InvalidTestEnvironmentException extends \Exception {}
