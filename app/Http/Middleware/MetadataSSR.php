<?php

namespace App\Http\Middleware;

use App\Services\PathResolverService;
use App\Services\PreviewGeneratorService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MetadataSSR {
    public function __construct(
        protected PathResolverService $pathResolver,
        protected PreviewGeneratorService $previewGenerator
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        if ($this->isSocialMediaBot($request) || $request->query('preview')) {
            return $this->previewGenerator->handle($request);
        }

        return $next($request);
    }

    protected function isSocialMediaBot(Request $request): bool {
        return $this->checkUserAgent($request) || $this->checkSocialHeaders($request);
    }

    protected function checkUserAgent(Request $request): bool {
        $userAgent = strtolower($request->header('User-Agent') ?? '');

        return preg_match('/bot|crawler|spider|facebookexternalhit|slackbot|twitterbot/i', $userAgent);
    }

    protected function checkSocialHeaders(Request $request): bool {
        $socialHeaders = [
            'X-Discord-Preview' => true,
            'X-Twitter-Referer' => true,
            'X-Facebook-External-Hit' => true,
            'X-Slack-No-Retry' => true,
            'X-Requested-With' => 'XMLHttpRequest',
        ];

        foreach ($socialHeaders as $header => $value) {
            if ($request->header($header) == $value) {
                return true;
            }
        }

        return false;
    }
}
