<?php

namespace App\Http\Middleware;

use App\Http\Resources\FolderResource;
use App\Http\Resources\VideoResource;
use App\Services\PathResolverService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class MetadataSSR {
    public function __construct(
        protected PathResolverService $pathResolver
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        if ($this->isSocialMediaBot($request)) {
            return $this->generatePreviewResponse($request);
        }

        return $next($request);
    }

    protected function generatePreviewResponse(Request $request): Response {
        $defaultData = [
            'title' => 'Media Server',
            'description' => 'An auto-generated preview for bots.',
            'secure_url' => asset('storage/thumbnails/default.webp'),
            'url' => $request->fullUrl(),
            'isAudio' => false,
            'mediaUrl' => null,
        ];

        try {
            $categoryIdentifier = $request->route('dir');
            $folderIdentifier = $request->route('folderName') ?? '';
            $videoId = $request->query('video');

            $category = $this->pathResolver->onlyPublic()->resolveCategory($categoryIdentifier);
            $folder = $this->pathResolver->resolveFolder(identifier: $folderIdentifier, category: $category);

            $folderResource = new FolderResource($folder);

            $thumbnailUrl = $folder->series->thumbnail_url
                ?? asset('storage/thumbnails/default.webp');

            $data = array_merge($defaultData, [
                'title' => Str::ucfirst($category->name) . " · {$folderResource->series->title}",
                'description' => $folder->series->description
                    ?? 'No description is available for this content.',
                'secure_url' => $thumbnailUrl,
            ]);

            $video = $videoId
                ? $folder->videos()->find($videoId)
                : null;

            if ($video) {
                $videoResource = new VideoResource($video);

                $isAudio = str_starts_with($videoResource->metadata?->mime_type, 'audio');
                $thumbnailUrl = $videoResource->metadata->poster_url
                    ? $videoResource->metadata->poster_url : $data['secure_url'];
                $data = array_merge($data, [
                    'title' => Str::ucfirst($folderResource->series->title) . " · {$video->metadata->title}",
                    'description' => $video->metadata->description
                        ?? $data['description'],
                    'secure_url' => $thumbnailUrl,
                    'isAudio' => $isAudio,
                    'duration' => $videoResource->metadata->duration, // For video/music duration tag
                    'release_date' => $video->metadata->date_released ?? $video->metadata->date_uploaded,
                    'mime_type' => $video->mime_type,
                ]);
            }

            $data['raw'] = $thumbnailUrl;

            return response()->view('og-preview', $data);
        } catch (\Throwable $th) {
            Log::warning('Error generating link preview.', [$th->getMessage()]);

            return response()->view('og-preview', $defaultData);
        }
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
