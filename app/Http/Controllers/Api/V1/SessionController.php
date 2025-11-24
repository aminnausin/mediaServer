<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller {
    public function index(Request $request) {
        $user = $request->user();
        $requestIp = $request->ip();
        $requestId = $request->hasSession() ? $request->session()->getId() : null;

        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) use ($requestId, $requestIp) {
                return [
                    'id' => $session->id,
                    'ip_address' => config('app.env') === 'demo' && $session->ip_address !== $requestIp ? 'hidden' : $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'is_current' => $session->id === $requestId,
                    'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                ];
            });

        return response()->json($sessions);
    }

    public function destroyOthers(Request $request) {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->delete();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        // This is not compatible with Sanctum or something -> Auth::logoutOtherDevices($validated['password']);

        return response()->noContent();
    }
}
