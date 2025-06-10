<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmailControllerTest extends TestCase {
    use RefreshDatabase;

    private const CHANGE_EMAIL_ENDPOINT = '/api/settings/email';

    public function test_user_can_update_email_with_correct_password() {
        $user = User::factory()->create([
            'email' => 'test@eccc.ca',
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user);

        $response = $this->putJson(self::CHANGE_EMAIL_ENDPOINT, [
            'email' => 'test2@eccc.ca',
            'password' => 'old-password',
        ]);

        $response->assertNoContent();
        $this->assertTrue($user->fresh()->email === 'test2@eccc.ca');
    }

    public function test_update_fails_with_incorrect_password() {
        $user = User::factory()->create([
            'email' => 'test@eccc.ca',
            'password' => Hash::make('current-password'),
        ]);

        $this->actingAs($user);

        $response = $this->putJson(self::CHANGE_EMAIL_ENDPOINT, [
            'email' => fake()->unique()->safeEmail,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    public function test_update_fails_if_email_exists() {
        $testUser = $this->getFakeUser();
        $otherUser = $this->getFakeUser();

        $user = User::factory()->create([
            'email' => $testUser['email'],
            'password' => Hash::make($testUser['password']),
        ]);

        User::factory()->create([
            'email' => $otherUser['email'],
            'password' => Hash::make($otherUser['password']),
        ]);

        $this->actingAs($user);

        $response = $this->putJson(self::CHANGE_EMAIL_ENDPOINT, [
            'email' => $otherUser['email'],
            'password' => $testUser['password'],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_update_fails_if_email_is_the_same() {
        $testUser = $this->getFakeUser();

        $user = User::factory()->create([
            'email' => $testUser['email'],
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user);

        $response = $this->putJson(self::CHANGE_EMAIL_ENDPOINT, [
            'email' => $testUser['email'],
            'password' => 'old-password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    private function getFakeUser(): array {
        return ['email' => fake()->unique()->safeEmail, 'password' => fake()->password(8)];
    }
}
