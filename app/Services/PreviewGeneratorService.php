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
    protected string $defaultThumbnail;

    public function __construct(
        protected PathResolverService $pathResolver,
    ) {
        $this->defaultThumbnail = asset('storage/thumbnails/default.webp');
    }

    public function handle(Request $request, bool $generateRawPreview): Response {
        $outputTemplate = $generateRawPreview ? 'og-media-preview' : 'og-preview';
        $defaultData = $this->defaultData($request);
        try {
            $categorySlug = $request->route('dir');
            $folderSlug = $request->route('folderName') ?? '';
            $videoId = $request->query('video');

            $category = $this->pathResolver->resolveCategory($categorySlug, $request->user()?->id !== 1);
            $folder = $this->pathResolver->resolveFolder(identifier: $folderSlug, category: $category)->load('series');

            if ($videoId) {
                $data = $this->buildVideoPreviewData($category, $folder, $videoId, $request, $generateRawPreview);
            } else {
                $data = $this->buildFolderPreviewData($category, $folder, $request, $generateRawPreview);
            }

            return response()->view($outputTemplate, $data);
        } catch (\Throwable $e) {
            Log::warning('Error generating link preview', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->view('og-preview', $defaultData);
        }
    }

    public function handleGenerateImage(array $data, string $relativePath, ?int $dataLastUpdated = 0, bool $queued = true): ?string {
        $disk = Storage::disk('public');
        $override = config('services.preview_generator.override');
        $relativePath = trim('previews/' . $relativePath, '/') . '.png';
        $fullPath = $disk->path($relativePath);

        try {
            if (! $disk->exists($relativePath)) {
                $queued = false;
            } elseif (! $override && $dataLastUpdated && filemtime($fullPath) > $dataLastUpdated) {
                return VerifyFiles::getPathUrl($relativePath);
            }

            if (! $override && $queued) {
                $fileJobService = app(FileJobService::class);
                $fileJobService->regeneratePreviewImages([['data' => $data, 'path' => $relativePath]]);

                return VerifyFiles::getPathUrl($relativePath);
            }

            $generatedImage = $this->generateImage($data, $relativePath);
            if (! $generatedImage) {
                throw new GenerateImageException('Failed to generate Image');
            }

            $disk->put($relativePath, $generatedImage);
        } catch (\Throwable $th) { // Cannot catch the specific spatie/image exception since it throws a generic one
            Log::warning('Error during OG image generation', ['error' => $th->getMessage()]);
        }

        return file_exists($fullPath)
            ? VerifyFiles::getPathUrl($relativePath)
            : null;
    }

    protected function buildFolderPreviewData(Category $category, ?Folder $folder, Request $request, bool $generateRawPreview): array {
        $folderResource = $this->getDecodedResource(new FolderResource($folder));
        $thumbnail = $folder->series->thumbnail_url ?: $this->defaultThumbnail;

        $isAudio = $folderResource->is_majority_audio;
        $fileCount = $folderResource->file_count ?? 0;
        $fileType = ($isAudio ? 'Track' : 'Episode') . ($fileCount === 1 ? '' : 's');
        $contentString = ($folderResource->series->started_at ? $this->getMediaReleaseSeason($folderResource->series->started_at) . ' • ' : '') . "$fileCount $fileType";
        $studio = ucfirst($folderResource?->series?->studio);

        $data = [
            'title' => "{$folderResource->series->title}",
            'description' => $folderResource->series->description ?: 'No description is available.',
            'studio' => ($studio ? $studio . ' · ' : '') . ucfirst($category->name),
            'is_audio' => $isAudio,
            'file_count' => $folderResource->file_count,
            'thumbnail_url' => $this->encodeImageURL($thumbnail),
            'upload_date' => $this->formatDate($folderResource->series->created_at),
            'content_string' => $contentString,
            'rating' => $folderResource->series->rating,
            'tags' => $folderResource->series->folder_tags ? array_map(fn ($tag) => $tag->name, $folderResource->series->folder_tags) : null,
            'url' => $request->fullUrl(),
        ];

        return $this->preparePreviewData($data, "folders/{$folder->id}", strtotime($folderResource->series?->updated_at ?? ''), $generateRawPreview);
    }

    protected function buildVideoPreviewData(Category $category, ?Folder $folder, string $videoId, Request $request, bool $generateRawPreview): array {
        $folderResource = $this->getDecodedResource(new FolderResource($folder));
        $video = $folder->videos()->findOrFail($videoId);
        $videoResource = $this->getDecodedResource(new VideoResource($video));

        $isAudio = str_starts_with($videoResource->metadata?->mime_type, 'audio');
        $thumbnail = $videoResource->metadata->poster_url ?: $folder->series->thumbnail_url ?: $this->defaultThumbnail;

        $releaseDate = $this->formatDate($video->metadata->released_at ?: $video->metadata->file_modified_at);
        $contentString = $releaseDate . ' • ' . $this->formatDuration($videoResource?->metadata?->duration ?? null);

        $folderDateUpdated = strtotime($folderResource->series->updated_at);
        $videoDateUpdated = strtotime($videoResource->updated_at ?? '') ?: 0;
        $latestTimestamp = max($folderDateUpdated, $videoDateUpdated);
        $data = [
            'title' => ucfirst($folderResource->series->title) . " · {$video->metadata->title}",
            'description' => $video->metadata->description ?: $folderResource->series->description ?: 'No description is available.',
            'thumbnail_url' => $this->encodeImageURL($thumbnail),
            'is_audio' => $isAudio,
            'content_string' => $contentString,
            'release_date' => $releaseDate,
            'upload_date' => $this->formatDate($video->metadata->file_modified_at),
            'mime_type' => $video->mime_type,
            'tags' => $videoResource->video_tags ? array_map(fn ($tag) => $tag->name, $videoResource->video_tags) : null,
            'studio' => ucfirst($folderResource?->series?->studio ?? $category->name),
            'url' => $request->fullUrl(),
        ];

        return $this->preparePreviewData($data, "{$folder->path}/{$video->id}", $latestTimestamp, $generateRawPreview);
    }

    protected function preparePreviewData(array $baseData, string $pathKey, ?int $dataLastUpdated, bool $generateRawPreview): array {
        if (! $generateRawPreview && $generatedImage = $this->handleGenerateImage($baseData, $pathKey, $dataLastUpdated)) {
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

            $chromeUserDataDir = storage_path('app/chrome');
            $chromeConfigHome = storage_path('app/chrome/.config');

            $html = $this->generatePreviewPage($data);
            $browsershot = Browsershot::html($html)->windowSize(1200, 630)
                ->deviceScaleFactor(2)
                ->waitUntilNetworkIdle()->setOption('args', [
                    '--disable-gpu',
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage',
                    "--user-data-dir=$chromeUserDataDir",
                    '--disable-web-security',
                    '--font-render-hinting=none',
                    '--enable-features=NetworkService,NetworkServiceInProcess',
                    '--blink-settings=imagesEnabled=true',
                ])
                ->ignoreConsoleErrors()->setEnvironmentOptions([
                    'CHROME_CONFIG_HOME' => $chromeConfigHome,
                ]);

            $browsershot = $this->setChromiumBinary($browsershot);
            $browsershot->timeout(120)->save($tempPath);

            $imageContents = file_get_contents($tempPath);
            Storage::disk('public')->delete($tempRelativePath);

            $result = $imageContents;

            if ($selfStore) {
                Storage::disk('public')->put($relativePath, $imageContents);
                $result = VerifyFiles::getPathUrl($relativePath);
            }

            return $result;
        } catch (\Throwable $th) { // Cannot catch the specific spatie/image exception since it throws a generic one
            $message = $th->getMessage();

            if ($message !== 'The spatie/image package is required to perform image manipulations. Please install it by running `composer require spatie/image`') {
                Log::error('Error during OG image generation', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);
            }

            if (file_exists($tempPath)) {
                $imageContents = file_get_contents($tempPath);
                Storage::disk('public')->delete($tempRelativePath);
            }

            if (! $selfStore || ! isset($imageContents)) {
                return $imageContents ?? false;
            }

            Storage::disk('public')->put($relativePath, $imageContents);

            return VerifyFiles::getPathUrl($relativePath);
        }
    }

    protected function canUseDocker(): bool {
        try {
            $dockerInfo = shell_exec('docker info --format "{{.ServerVersion}}" 2>&1');

            return ! empty($dockerInfo) && ! str_contains(strtolower($dockerInfo), 'error');
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function getPuppeteerChromiumPath(): string {
        try {
            // Determine user home directory
            $homeDir = getenv('HOME') ?: getenv('USERPROFILE');
            if (! $homeDir) {
                throw new ChromiumException('Unable to resolve home directory.');
            }

            // Chromium cache path
            $cacheDir = $homeDir . '/.cache/puppeteer/chrome';
            if (! is_dir($cacheDir)) {
                throw new ChromiumException("Chromium not found in Puppeteer cache: {$cacheDir}");
            }

            return $this->resolveChromiumBinary($cacheDir);
        } catch (\Throwable $th) {
            Log::warning('Puppeteer Chromium path not found', ['Error' => $th->getMessage()]);

            return '';
        }
    }

    protected function resolveChromiumBinary(string $baseDir): ?string {
        try {
            $platform = PHP_OS_FAMILY;

            if (! is_dir($baseDir) || ! ($versions = glob($baseDir . '/*', GLOB_ONLYDIR)) || empty($versions[0])) {
                throw new ChromiumException('Invalid chromium binary query');
            }

            foreach ($versions as $versionDir) {
                $binary = match ($platform) {
                    'Windows' => $versionDir . '/chrome-win64/chrome.exe',
                    'Darwin' => $versionDir . '/chrome-mac/Chromium.app/Contents/MacOS/Chromium',
                    default => $versionDir . '/chrome-linux64/chrome',
                };

                if (! file_exists($binary)) {
                    throw new ChromiumException("Chromium binary not found at: {$binary}");
                }

                return $binary;
            }

            throw new ChromiumException('No chromium binary found');
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function setChromiumBinary(Browsershot $browsershot) {
        if (file_exists('/run/current-system/sw/bin/chromium')) {
            $browsershot->setChromePath('/run/current-system/sw/bin/chromium');
        } elseif (file_exists('/usr/bin/chromium') || file_exists('/usr/bin/chromium-browser')) {
            $browsershot->setChromePath('/usr/bin/chromium');
        } elseif ($this->canUseDocker()) {
            $browsershot->useDocker();
        } elseif ($this->getPuppeteerChromiumPath()) {
            return $browsershot;
        } else {
            throw new ChromiumException('No Chromium or Docker available for Browsershot.');
        }

        return $browsershot;
    }

    protected function generatePreviewPage(array $data): string {
        $appURL = config('app.host', 'app.test');

        $appScheme = config('app.scheme');
        $appSchemeURL = "$appScheme://$appURL";
        $nonAppSchemeURL = ($appScheme === 'https' ? 'http' : 'https') . '://' . $appURL;
        $internalURL = $appURL . ':' . config('app.port', $appScheme === 'https' ? 443 : 80);

        $html = view('og-media-preview', $data)->render();

        // Have to manage what the local url is and effectively replace the public url. Chrome on the server system may not see the public url if any proxy is done on another system

        $html = str_replace($nonAppSchemeURL, $appSchemeURL, $html); // replacing external scheme with internal scheme (http for docker, https for standard)
        $html = str_replace($appURL . '/', $internalURL . '/', $html); // replacing external port (80 or 443) with internal port (8080 on docker or 443 on standard)

        return $html;
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
        } catch (\Throwable) {
            return null;
        }
    }

    protected function defaultData(Request $request): array {
        return [
            'title' => 'Media Server',
            'description' => 'An auto-generated preview for bots.',
            'thumbnail_url' => $this->defaultThumbnail,
            'url' => $request->fullUrl(),
            'is_audio' => false,
            'mediaUrl' => null,
        ];
    }

    protected function getDecodedResource($resource) {
        return json_decode(json_encode($resource));
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

    protected function encodeImageURL(string $url): string {
        return str_replace([chr(39), chr(34), ' '], ['%27', '%22', '%20'], $url);
    }
}

class ChromiumException extends Exception {}

class GenerateImageException extends Exception {}
