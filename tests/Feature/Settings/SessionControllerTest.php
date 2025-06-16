<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SessionControllerTest extends TestCase {
    use RefreshDatabase;

    public function test_returns_sessions_for_authenticated_user_only() {
        $baseSession = $this->getBaseSession();
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $sessionId = Str::random(40);

        // Create a session manually
        DB::table('sessions')->insert(
            array_merge(['id' => $sessionId, 'user_id' => $user->id], $baseSession),
            array_merge(['id' => Str::random(40), 'user_id' => $user2->id], $baseSession),
        );

        // Create a request manually
        $request = Request::create('/fake', 'GET', [], [
            config('session.cookie') => $sessionId,
        ]);
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession(app('session.store'));
        app('session.store')->setId($sessionId);
        app('session.store')->start();

        $controller = new \App\Http\Controllers\Api\V1\SessionController;
        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'id' => $sessionId,
            'ip_address' => $baseSession['ip_address'],
            'user_agent' => $baseSession['user_agent'],
            'is_current' => true,
        ]);
    }

    public function test_unauthenticated_users_cannot_access_sessions() {
        $response = $this->getJson('/api/settings/sessions');
        $response->assertUnauthorised(); // 401
    }

    private function getBaseSession(): array {
        return [
            'ip_address' => '127.0.0.1',
            'user_agent' => 'FakeAgent/1.0',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ];
    }
}
