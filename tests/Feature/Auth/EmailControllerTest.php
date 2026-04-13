<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailControllerTest extends TestCase {
    use RefreshDatabase;

    private User $user;

    private const API_ENDPOINT = '/api/settings/email';

    private const NEW_EMAIL = 'new@example.com';

    private const PASSWORD = 'password';

    private const PAYLOAD = [
        'email' => self::NEW_EMAIL,
        'password' => self::PASSWORD,
    ];

    protected function setUp(): void {
        parent::setUp();
        $this->user = User::factory()->create([
            'email' => 'original@example.com',
            'password' => bcrypt(self::PASSWORD),
        ]);
    }

    private function act(array $payload = []): \Illuminate\Testing\TestResponse {
        return $this->actingAs($this->user)
            ->putJson(self::API_ENDPOINT, [...self::PAYLOAD, ...$payload]);
    }

    public function test_user_can_change_email(): void {
        $this->act()->assertNoContent();

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'email' => self::NEW_EMAIL,
        ]);
    }

    public function test_rejects_wrong_password(): void {
        $this->act(['password' => 'wrongpassword'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_rejects_same_email(): void {
        $this->act(['email' => 'original@example.com'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_rejects_email_already_taken(): void {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->act(['email' => 'taken@example.com'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_email_is_lowercased(): void {
        $this->act(['email' => strtoupper(self::NEW_EMAIL)])->assertNoContent();

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'email' => self::NEW_EMAIL,
        ]);
    }

    public function test_rejects_unauthenticated(): void {
        $this->putJson(self::API_ENDPOINT, self::PAYLOAD)->assertUnauthorized();
    }
}
