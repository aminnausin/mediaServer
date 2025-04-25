<?php

namespace App\Services;

use App\Http\Resources\FolderResource;
use App\Http\Resources\VideoResource;
use App\Jobs\GeneratePreviewImage;
use App\Jobs\VerifyFiles;
use App\Models\Category;
use App\Models\Folder;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\Response;

class PreviewGeneratorService {
    public function __construct(
        protected PathResolverService $pathResolver,
    ) {
    }

    public function handle(Request $request): Response {
        $defaultData = $this->defaultData($request);

        try {
            $categorySlug = $request->route('dir');
            $folderSlug = $request->route('folderName') ?? '';
            $videoId = $request->query('video');

            $category = $this->pathResolver->onlyPublic()->resolveCategory($categorySlug);
            $folder = $this->pathResolver->resolveFolder(identifier: $folderSlug, category: $category)->load('series');

            if ($videoId) {
                $data = $this->buildVideoPreviewData($category, $folder, $videoId, $request);
            } else {
                $data = $this->buildFolderPreviewData($category, $folder, $request);
            }

            return response()->view('og-preview', $data);
        } catch (\Throwable $e) {
            dd([
                'error' => $e->getMessage(),
            ]);
            Log::warning('Error generating link preview', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->view('og-preview', $defaultData);
        }
    }

    public function handleGenerateImage(array $data, string $relativePath, ?string $dataLastUpdated = null, bool $queued = false): string {
        try {
            $relativePath = trim('previews/' . $relativePath, '/') . '.png';
            $fullPath = Storage::disk('public')->path($relativePath);

            if (! Storage::disk('public')->exists($relativePath)) {
                $queued = false;
            } elseif (Storage::disk('public')->exists($relativePath) && ! is_null($dataLastUpdated) && filemtime($fullPath) > strtotime($dataLastUpdated)) {
                return VerifyFiles::getPathUrl($relativePath);
            }

            if ($queued) {
                GeneratePreviewImage::dispatch($data, $relativePath);

                return asset('storage/thumbnails/default.webp');
            }

            $generatedImage = $this->generateImage($data, $relativePath);
            if (! $generatedImage) {
                throw new Exception('Failed to generate Image');
            }
            Storage::disk('public')->put($relativePath, $generatedImage);

            return VerifyFiles::getPathUrl($relativePath);
        } catch (\Throwable $th) { // Cannot catch the specific spatie/image exception since it throws a generic one
            Log::warning('Error during OG image generation', ['error' => $th->getMessage()]);

            return file_exists($fullPath)
                ? VerifyFiles::getPathUrl($relativePath)
                : asset('storage/thumbnails/default.webp');
        }
    }

    protected function buildFolderPreviewData(Category $category, ?Folder $folder, Request $request): array {
        $folderResource = $this->getDecodedResource(new FolderResource($folder));
        $thumbnail = $folder->series->thumbnail_url ?? asset('storage/thumbnails/default.webp');

        $data = [
            'title' => ucfirst($category->name) . " · {$folderResource->series->title}",
            'description' => $folderResource->series->description ?? 'No description is available.',
            'studio' => ucfirst($folderResource->series->studio),
            'file_count' => $folderResource->file_count,
            'thumbnail_url' => $thumbnail,
            'date_start' => $this->getMediaReleaseSeason($folderResource->series->date_start ?? ''),
            'url' => $request->fullUrl(),
        ];

        $data['raw'] = $this->handleGenerateImage($data, "folders/{$folder->id}", $folderResource->series?->date_updated);

        return $data;
    }

    protected function buildVideoPreviewData(Category $category, ?Folder $folder, string $videoId, Request $request): array {
        $folderResource = $this->getDecodedResource(new FolderResource($folder));
        $video = $folder->videos()->findOrFail($videoId);
        $videoResource = $this->getDecodedResource(new VideoResource($video));
        $isAudio = str_starts_with($videoResource->metadata?->mime_type, 'audio');
        $thumbnail = $videoResource->metadata->poster_url ?? $folder->series->thumbnail_url ?? asset('storage/thumbnails/default.webp');

        $data = [
            'title' => ucfirst($folderResource->series->title) . " · {$video->metadata->title}",
            'description' => $video->metadata->description ?? $folderResource->series->description ?? 'No description is available.',
            'thumbnail_url' => $thumbnail,
            'is_audio' => $isAudio,
            'duration' => $videoResource->metadata->duration,
            'release_date' => $video->metadata->date_released ?? $video->metadata->date_uploaded,
            'mime_type' => $video->mime_type,
            'tags' => $videoResource->video_tags ? array_map(fn($tag) => $tag->name, $videoResource->video_tags) : null,
            'studio' => ucfirst($folderResource->series->studio),
            'url' => $request->fullUrl(),
        ];

        $data['raw'] = $this->handleGenerateImage($data, "{$folder->path}/{$video->id}", $videoResource?->date_updated);

        return $data;
    }

    public function generateImage(array $data, string $relativePath) {
        try {
            $tempPath = Storage::disk('public')->path(uniqid('og-', true) . '.png');

            Storage::disk('public')->makeDirectory(dirname($relativePath));

            $html = view('og-media-preview', $data)->render();

            $browsershot = Browsershot::html($html)->windowSize(1200, 630)
                ->deviceScaleFactor(2)
                ->waitUntilNetworkIdle()->setOption('args', ['--no-sandbox'])
                ->ignoreConsoleErrors();

            if ($this->canUseDocker()) {
                $browsershot->useDocker();
            } elseif (file_exists('/usr/bin/chromium') || file_exists('/usr/bin/chromium-browser')) {
                $browsershot->setChromePath('/usr/bin/chromium');
            } else {
                throw new \RuntimeException('No Chromium or Docker available for Browsershot.');
            }
            $browsershot->save($tempPath);

            $imageContents = file_get_contents($tempPath);
            unlink($tempPath);

            return $imageContents;
        } catch (\Throwable $th) { // Cannot catch the specific spatie/image exception since it throws a generic one
            Log::warning('Error during OG image generation', ['error' => $th->getMessage()]);
            if (file_exists($tempPath)) {
                $imageContents = file_get_contents($tempPath);
                @unlink($tempPath);
            }

            return $imageContents ?? false;
        }
    }

    protected function getMediaReleaseSeason(?string $dateString): ?string {
        if (! $dateString) {
            return null;
        }

        try {
            $date = Carbon::parse($dateString);
            $season = match (true) {
                $date->month >= 1 && $date->month <= 3 => 'Winter ',
                $date->month >= 4 && $date->month <= 6 => 'Spring ',
                $date->month >= 7 && $date->month <= 9 => 'Summer ',
                default => 'Fall ',
            };

            return $season . $date->year;
        } catch (\Exception) {
            return null;
        }
    }

    protected function defaultData(Request $request): array {
        return [
            'title' => 'Media Server',
            'description' => 'An auto-generated preview for bots.',
            'thumbnail_url' => asset('storage/thumbnails/default.webp'),
            'url' => $request->fullUrl(),
            'is_audio' => false,
            'mediaUrl' => null,
        ];
    }

    protected function getDecodedResource($resource) {
        return json_decode(json_encode($resource));
    }

    protected function canUseDocker(): bool {
        try {
            $output = shell_exec('docker version --format "{{.Server.Version}}" 2>&1');

            return ! str_contains(strtolower($output), 'error') && trim($output) !== '';
        } catch (\Throwable $e) {
            return false;
        }
    }
}
