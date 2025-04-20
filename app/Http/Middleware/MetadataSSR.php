<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\V1\PreviewController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MetadataSSR {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        if ($this->isSocialMediaBot($request)) {
            return app(PreviewController::class)->showPreview($request);
        }

        return $next($request);
    }

    protected function isSocialMediaBot(Request $request): bool {
        $userAgent = strtolower($request->header('User-Agent') ?? '');

        if (preg_match('/bot|crawler|spider|facebookexternalhit|slackbot|twitterbot|discordbot|telegrambot|whatsapp|linkedinbot|mastodon|redditbot|pinterest/i', $userAgent)) {
            return true;
        }

        $socialHeaders = [
            'X-Discord-Preview' => true,  // Discord sends this
            'X-Twitter-Referer' => true,  // Twitter/X
            'X-Facebook-External-Hit' => true,  // Facebook
            'X-Slack-No-Retry' => true,  // Slack
            'X-Requested-With' => 'XMLHttpRequest',  // Some bots
        ];

        foreach ($socialHeaders as $header => $value) {
            if ($request->header($header) == $value) {
                return true;
            }
        }

        return false;
    }
}
