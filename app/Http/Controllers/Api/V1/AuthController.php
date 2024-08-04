<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use HttpResponses;

    public function create(): View
    {
        return view('auth.login');
    }

    
    public function generate(): View
    {
        return view('auth.register');
    }
    /**
     * Attempt to authenticate the request's credentials.
     */
    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());
        if(!Auth::attempt($request->only('email', 'password'),$request->remember_me)){
            return $request->expectsJson() ? $this->error('','Invalid Credentials', 401) : view('auth.login', array("error"=>"Invalid Credentials"));
        }
        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('API token for ' . $user->name)->plainTextToken;

        if ($request->expectsJson()){

            return $this->success([
                'user' => $user,
                'token' => $token
            ]);
        }
        if (! $request->expectsJson()) {
            $request->session()->regenerate();

            return redirect()->intended();
        }
    }

    public function register(StoreUserRequest $request)
    {
        $request->validated($request->all());

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);


        if ($request->expectsJson()){

            Auth::login($user);
            $token = $user->createToken('API token for ' . $user->name)->plainTextToken;

            return $this->success([
                'user'=>$user, 
                'token'=>$token
            ]);
        }
        if (! $request->expectsJson()) {
            return redirect()->intended('/login');
        }
    }

    public function authenticate()
    {
        $user = Auth::user();

        return $this->success(array('user'=>$user),'Authenticated as ' . $user->name);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return $this->success($request->session()->token(), 'Log out successful.');
    }
}
