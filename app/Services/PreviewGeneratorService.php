<?php

namespace App\Services;

use App\Http\Resources\FolderResource;
use App\Http\Resources\VideoResource;
use App\Jobs\VerifyFiles;
use App\Models\Category;
use App\Models\Folder;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\Response;

class PreviewGeneratorService {
    public function __construct(
        protected PathResolverService $pathResolver,
        protected FileJobService $fileJobService,
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
            Log::warning('Error generating link preview', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->view('og-preview', $defaultData);
        }
    }

    public function handleGenerateImage(array $data, string $relativePath, ?int $dataLastUpdated = 0, bool $queued = true): ?string {
        try {
            $override = false; // config('services.preview_generator.override');
            $relativePath = trim('previews/' . $relativePath, '/') . '.png';
            $fullPath = Storage::disk('public')->path($relativePath);

            if (! Storage::disk('public')->exists($relativePath)) {
                $queued = false;
            } elseif (! $override && Storage::disk('public')->exists($relativePath) && ! is_null($dataLastUpdated) && filemtime($fullPath) > $dataLastUpdated) {
                return VerifyFiles::getPathUrl($relativePath);
            }
            if (! $override && $queued) {
                $this->fileJobService->regeneratePreviewImages([['data' => $data, 'path' => $relativePath]]);

                return VerifyFiles::getPathUrl($relativePath);
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
                : null;
        }
    }

    protected function buildFolderPreviewData(Category $category, ?Folder $folder, Request $request): array {
        $folderResource = $this->getDecodedResource(new FolderResource($folder));
        $thumbnail = $folder->series->thumbnail_url ?: asset('storage/thumbnails/default.webp');

        $isAudio = $folder->isMajorityAudio();
        $fileCount = $folderResource->file_count ?? 0;
        $fileType = ($isAudio ? 'Track' : 'Episode') . ($fileCount === 1 ? '' : 's');
        $contentString = ($folderResource->series->date_start ? $this->getMediaReleaseSeason($folderResource->series->date_start) . ' • ' : '') . "$fileCount $fileType";
        $studio = ucfirst($folderResource?->series?->studio);

        $data = [
            'title' => "{$folderResource->series->title}",
            'description' => $folderResource->series->description ?: 'No description is available.',
            'studio' => ($studio ? $studio . ' · ' : '') . ucfirst($category->name),
            'is_audio' => $isAudio,
            'file_count' => $folderResource->file_count,
            'thumbnail_url' => $thumbnail,
            'upload_date' => $this->formatDate($folderResource->series->date_created),
            'content_string' => $contentString,
            'tags' => $folderResource->series->folder_tags ? array_map(fn($tag) => $tag->name, $folderResource->series->folder_tags) : null,
            'url' => $request->fullUrl(),
        ];

        return $this->preparePreviewData($data, "folders/{$folder->id}", strtotime($folderResource->series?->date_updated ?? ''));
    }

    protected function buildVideoPreviewData(Category $category, ?Folder $folder, string $videoId, Request $request): array {
        $folderResource = $this->getDecodedResource(new FolderResource($folder));
        $video = $folder->videos()->findOrFail($videoId);
        $videoResource = $this->getDecodedResource(new VideoResource($video));

        $isAudio = str_starts_with($videoResource->metadata?->mime_type, 'audio');
        $thumbnail = $videoResource->metadata->poster_url ?: $folder->series->thumbnail_url ?: asset('storage/thumbnails/default.webp');

        $releaseDate = $this->formatDate($video->metadata->date_released ?: $video->metadata->date_uploaded);
        $contentString = $releaseDate . ' • ' . $this->formatDuration($videoResource?->metadata?->duration ?? null);

        $folderDateUpdated = strtotime($folderResource->series->date_updated);
        $videoDateUpdated = strtotime($videoResource->date_updated ?? '') ?: 0;
        $latestTimestamp = max($folderDateUpdated, $videoDateUpdated);
        $data = [
            'title' => ucfirst($folderResource->series->title) . " · {$video->metadata->title}",
            'description' => $video->metadata->description ?: $folderResource->series->description ?: 'No description is available.',
            'thumbnail_url' => $thumbnail,
            'is_audio' => $isAudio,
            'content_string' => $contentString,
            'release_date' => $releaseDate,
            'upload_date' => $this->formatDate($video->metadata->date_uploaded),
            'mime_type' => $video->mime_type,
            'tags' => $videoResource->video_tags ? array_map(fn($tag) => $tag->name, $videoResource->video_tags) : null,
            'studio' => ucfirst($folderResource?->series?->studio),
            'url' => $request->fullUrl(),
        ];

        return $this->preparePreviewData($data, "{$folder->path}/{$video->id}", $latestTimestamp);
    }

    protected function preparePreviewData(array $baseData, string $pathKey, ?int $dataLastUpdated): array {
        if ($generatedImage = $this->handleGenerateImage($baseData, $pathKey, $dataLastUpdated)) {
            $baseData['thumbnail_url'] = $generatedImage;
            $baseData['is_generated'] = true;
        }

        $baseData['raw'] = $baseData['thumbnail_url'];

        return $baseData;
    }

    public function generateImage(array $data, string $relativePath, bool $selfStore = false) {
        try {
            $tempRelativePath = 'previews/' . uniqid('og-', true) . '.png';
            $tempPath = Storage::disk('public')->path($tempRelativePath);

            Storage::disk('public')->makeDirectory(dirname($relativePath));

            $html = view('og-media-preview', $data)->render();

            $browsershot = Browsershot::html($html)->windowSize(1200, 630)
                ->deviceScaleFactor(2)
                ->waitUntilNetworkIdle()->setOption('args', [
                    '--no-sandbox',
                    '--user-data-dir=/tmp/chromium-profile',
                ])
                ->ignoreConsoleErrors()->setEnvironmentOptions([
                    'CHROME_CONFIG_HOME' => storage_path('app/chrome/.config'),
                ]);
            if (file_exists('/run/current-system/sw/bin/chromium')) {
                $browsershot->setChromePath('/run/current-system/sw/bin/chromium');
            } elseif ($this->canUseDocker()) {
                $browsershot->useDocker();
            } elseif (file_exists('/usr/bin/chromium') || file_exists('/usr/bin/chromium-browser')) {
                $browsershot->setChromePath('/usr/bin/chromium');
            } else {
                throw new \RuntimeException('No Chromium or Docker available for Browsershot.');
            }
            $browsershot->save($tempPath);

            $imageContents = file_get_contents($tempPath);
            Storage::disk('public')->delete($tempRelativePath);

            if (! $selfStore) {
                return $imageContents;
            }

            Storage::disk('public')->put($relativePath, $imageContents);

            return VerifyFiles::getPathUrl($relativePath);
        } catch (\Throwable $th) { // Cannot catch the specific spatie/image exception since it throws a generic one
            $message = $th->getMessage();
            if ($message !== 'The spatie/image package is required to perform image manipulations. Please install it by running `composer require spatie/image`') {
                Log::error('Error during OG image generation', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);
            }

            if (file_exists($tempPath)) {
                $imageContents = file_get_contents($tempPath);
                Storage::disk('public')->delete($tempRelativePath);
            }

            if (!$selfStore || !isset($imageContents)) {
                return $imageContents ?? false;
            }

            Storage::disk('public')->put($relativePath, $imageContents);
            return VerifyFiles::getPathUrl($relativePath);
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
            $dockerInfo = shell_exec('docker info --format "{{.ServerVersion}}" 2>&1');

            return ! empty($dockerInfo) && ! str_contains(strtolower($dockerInfo), 'error');
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function formatDate(?string $date, ?string $errorMessage = 'Unknown Date') {
        if (! $date) {
            return $errorMessage;
        }

        return Carbon::createFromDate($date)->format('F Y');
    }

    protected function formatDuration(?int $seconds) {
        if (! $seconds) {
            return 'Unknown Duration';
        }

        return CarbonInterval::seconds($seconds)->cascade()->forHumans([
            'short' => true,
        ]);
    }
}
