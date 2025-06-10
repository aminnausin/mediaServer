<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordControllerTest extends TestCase {
    use RefreshDatabase;

    private const CHANGE_PASSWORD_ENDPOINT = '/api/settings/password';

    public function test_user_can_update_password_with_correct_current_password() {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user);

        $response = $this->putJson(self::CHANGE_PASSWORD_ENDPOINT, [
            'current_password' => 'old-password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

        $response->assertNoContent();
        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }

    public function test_update_fails_with_incorrect_current_password() {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user);

        $response = $this->putJson(self::CHANGE_PASSWORD_ENDPOINT, [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('current_password');
    }

    public function test_update_fails_if_passwords_do_not_match() {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user);

        $response = $this->putJson(self::CHANGE_PASSWORD_ENDPOINT, [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'wrong-confirmation',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    public function test_update_fails_if_password_does_not_meet_requirements() {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user);

        $response = $this->putJson(self::CHANGE_PASSWORD_ENDPOINT, [
            'current_password' => 'old-password',
            'password' => 'short', // invalid with Password::defaults() (not long enough)
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }
}
