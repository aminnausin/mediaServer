<?php

namespace App\Http\Middleware;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;

class UserLastActive {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next) {
        if (Auth::check()) {
            User::where('id', Auth::user()->id)->update(['last_active' => Carbon::now()]);
        }

        return $next($request);
    }
}
