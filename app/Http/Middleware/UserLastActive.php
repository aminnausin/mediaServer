<?php

namespace App\Http\Middleware;

use Closure;

class UserLastActive {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next) {
        $request->user()?->update(['last_active' => now()]);

        return $next($request);
    }
}
