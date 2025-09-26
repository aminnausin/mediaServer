<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller {
    use HttpResponses;

    /**
     * Attempt to authenticate the request's credentials_me.
     */
    public function login(UserLoginRequest $request) {
        $validated = $request->validated();

        if (! Auth::attempt($request->only('email', 'password'), $request['remember'])) {
            return $this->invalidCredentialsResponse();
        }

        $user = User::where('email', $validated['email'])->first(); // Should remove
        $token = $user->createToken('API token for ' . $user->name)->plainTextToken; // Should remove

        $request->session()->regenerate();

        return response()->json([
            'user' => new UserResource(Auth::user()),
            'token' => $token, // Should remove
        ]);
    }

    public function register(UserStoreRequest $request) {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if ($request->expectsJson()) {
            Auth::login($user);
            $token = $user->createToken('API token for ' . $user->name)->plainTextToken;

            return $this->success([
                'user' => $user,
                'token' => $token,
            ]);
        }

        return redirect()->intended('/login');
    }

    public function authenticate() {
        try {
            $user = Auth::user();

            return $this->success(['user' => $user], 'Authenticated as ' . $user->name);
        } catch (\Throwable $th) {
            return response(['error' => $th->getMessage(), 'session' => env('SESSION_DOMAIN'), 'sanctum' => env('SANCTUM_STATEFUL_DOMAINS'), 'app' => env('APP_URL')], 401);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request) {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return $this->success($request->session()->token(), 'Log out successful.');
    }

    private function invalidCredentialsResponse() {
        return response()->json([
            'errors' => ['email' => ['Invalid credentials.']],
        ], 422);
    }
}
