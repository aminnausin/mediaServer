<?php

namespace App\Http\Controllers\Proxy;

use App\Http\Controllers\Controller;
use App\Jobs\Proxy\AsyncPlausibleEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PlausibleProxyController extends Controller {
    const EVENT_CONTENT_TYPE = 'application/json';

    public function script(): Response {
        $scriptUrl = config('services.plausible.url');
        if (! $scriptUrl) {
            abort(404);
        }

        $script = Cache::remember('plausible-script', now()->addHours(24), function () use ($scriptUrl) {
            $response = Http::get($scriptUrl);
            if (! $response->successful()) {
                throw new \RuntimeException('Failed to fetch Plausible script');
            }

            return $response->body();
        });

        return response($script, 200, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'public, max-age=2592000', // 3d cache
        ]);
    }

    public function event(Request $request): Response {
        $baseUrl = config('services.plausible.domain');
        if (! $baseUrl) {
            abort(404);
        }

        $size = (int) $request->header('Content-Length');
        if ($size > 2048) {
            abort(413);
        }

        // If I move to Octane, this can be done concurrently like in other backends instead of using the queue
        AsyncPlausibleEvent::dispatch(
            body: $request->getContent(),
            headers: [
                'User-Agent' => $request->userAgent(),
                'X-Forwarded-For' => $request->ip(),
                'Content-Type' => 'application/json',
            ],
        );

        return response('ok', 202, ['Content-Type' => self::EVENT_CONTENT_TYPE]);
    }
}
