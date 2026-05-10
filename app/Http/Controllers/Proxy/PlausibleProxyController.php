<?php

namespace App\Http\Controllers\Proxy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PlausibleProxyController extends Controller {
    public function script(): Response {
        $scriptUrl = config('services.plausible.url');
        if (! $scriptUrl) {
            abort(404);
        }

        $script = Cache::remember('plausible-script', now()->addHours(24), function () use ($scriptUrl) {
            $response = Http::get($scriptUrl);
            if (!$response->successful()) {
                throw new \RuntimeException('Failed to fetch Plausible script');
            }

            return $response->body();
        });

        return response($script, 200, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function event(Request $request): Response {
        $baseUrl = config('services.plausible.domain');
        if (! $baseUrl) {
            abort(404);
        }

        $response = Http::withHeaders([
            'User-Agent' => $request->userAgent(),
            'X-Forwarded-For' => $request->ip(),
            'Content-Type' => 'application/json',
        ])->withBody($request->getContent(), 'application/json')->post(config('services.plausible.domain') . '/api/event');

        return response($response->body(), $response->status());
    }
}
