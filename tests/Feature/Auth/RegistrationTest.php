<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase {
    use RefreshDatabase;

    private const VALID_EMAIL = 'user@example.com';

    private const API_ENDPOINT = '/api/register';

    private function validPayload(array $overrides = []): array {
        return array_merge([
            'name' => 'validuser',
            'email' => self::VALID_EMAIL,
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ], $overrides);
    }

    public function test_user_can_register(): void {
        $response = $this->postJson(self::API_ENDPOINT, $this->validPayload());

        $response->assertStatus(200)->assertJsonStructure(['user', 'token']);
        $this->assertDatabaseHas('users', ['email' => self::VALID_EMAIL]);
    }

    public function test_name_must_contain_a_letter(): void {
        $this->postJson(self::API_ENDPOINT, $this->validPayload(['name' => '1234']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_name_rejects_spaces(): void {
        $this->postJson(self::API_ENDPOINT, $this->validPayload(['name' => 'valid user']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_name_rejects_consecutive_special_characters(): void {
        $this->postJson(self::API_ENDPOINT, $this->validPayload(['name' => 'valid--user']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_name_rejects_leading_special_characters(): void {
        $this->postJson(self::API_ENDPOINT, $this->validPayload(['name' => '-validuser']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_name_rejects_trailing_special_characters(): void {
        $this->postJson(self::API_ENDPOINT, $this->validPayload(['name' => 'validuser_']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_name_rejects_reserved_words(): void {
        foreach (['admin', 'profile', 'settings', 'login', 'register'] as $reserved) {
            $this->postJson(self::API_ENDPOINT, $this->validPayload(['name' => $reserved]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('name');
        }
    }

    public function test_name_is_unique(): void {
        User::factory()->create(['name' => 'existinguser']);

        $this->postJson(self::API_ENDPOINT, $this->validPayload(['name' => 'existinguser']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_name_unique_check_is_case_insensitive(): void {
        User::factory()->create(['name' => 'existinguser']);

        $this->postJson(self::API_ENDPOINT, $this->validPayload(['name' => 'ExistingUser']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_email_must_be_unique(): void {
        User::factory()->create(['email' => self::VALID_EMAIL]);

        $this->postJson(self::API_ENDPOINT, $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_password_must_be_confirmed(): void {
        $this->postJson(self::API_ENDPOINT, $this->validPayload(['password_confirmation' => 'wrong']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }
}
