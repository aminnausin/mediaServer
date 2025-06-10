<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SessionControllerTest extends TestCase {
    use RefreshDatabase;

    public function returns_sessions_for_authenticated_user_only() {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $this->actingAs($user);

        // Create a session manually
        DB::table('sessions')->insert(
            [
                'id' => $sessionId = Str::random(40),
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'FakeAgent/1.0',
                'payload' => '',
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => $sessionId = Str::random(40),
                'user_id' => $user2->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'FakeAgent/1.0',
                'payload' => '',
                'last_activity' => now()->timestamp,
            ]
        );

        // Set current session ID
        session()->setId($sessionId);
        session()->start();

        $response = $this->getJson('/api/settings/sessions');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id' => $sessionId,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'FakeAgent/1.0',
                'is_current' => true,
            ]);
    }

    public function unauthenticated_users_cannot_access_sessions() {
        $response = $this->getJson('/api/settings/sessions');
        $response->assertUnauthorized(); // 401
    }
}
