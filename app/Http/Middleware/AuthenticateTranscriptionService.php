<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateTranscriptionService {
    public function handle(Request $request, Closure $next): Response {
        $expected = config('services.transcribe.key');
        $provided = $request->bearerToken();

        if (! is_string($expected) || $expected === '' || ! is_string($provided) || ! hash_equals($expected, $provided)) {
            abort(401, 'Unauthenticated.');
        }

        return $next($request);
    }
}
