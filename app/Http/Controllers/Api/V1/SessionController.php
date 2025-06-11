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
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) use ($request) {
                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'is_current' => $request->hasSession() && $session->id === $request->session()->getId(),
                    'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                ];
            });

        return response()->json($sessions);
    }

    public function delete(Request $request) {
        $validated = $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        Auth::logoutOtherDevices($validated['password']);

        return response()->noContent();
    }
}
