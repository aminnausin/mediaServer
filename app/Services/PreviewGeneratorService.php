<?php

namespace App\Services;

use App\Data\Images\ImageData;
use App\Enums\ImageSource;
use App\Enums\ImageType;
use App\Enums\MediaType;
use App\Enums\TaskStatus;
use App\Models\Category;
use App\Models\Folder;
use App\Models\Image;
use App\Models\Metadata;
use App\Models\Series;
use App\Models\SubTask;
use App\Services\Images\ImageService;
use App\Services\Puppeteer\ChromiumResolver;
use App\Support\MediaFormatter;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\Response;

class PreviewGeneratorService {
    protected string $defaultPoster;

    private const LEGACY_PREVIEW_PREFIX = 'previews/';

    public function __construct(
        protected PathResolverService $pathResolver,
        protected ChromiumResolver $chromiumResolver,
        protected ImageService $imageService,
    ) {
        $this->defaultPoster = asset('storage/thumbnails/default.webp'); // I should use this more often???
    }

    public function handle(Request $request, bool $generateRawPreview): Response {
        $outputTemplate = $generateRawPreview ? 'og-media-preview' : 'og-preview';
        $defaultData = $this->defaultData($request);

        try {
            $categorySlug = $request->route('dir');
            $folderSlug = $request->route('folderName') ?? '';
            $videoId = $request->query('video');

            if ($videoId) {
                $metadata = Metadata::where('video_id', $videoId)->firstOrFail()->load('video.folder.series.primaryPoster', 'video.folder.category', 'videoTags', 'primaryPoster');
                $viewData = $this->preparePreviewData($this->buildMediaPreviewData($metadata, $request), $metadata, $generateRawPreview);
            } else {
                $category = $this->pathResolver->resolveCategory($categorySlug, ! Gate::allows('admin'));
                $folder = $this->pathResolver->resolveFolder(identifier: $folderSlug, category: $category, onlyPublic: ! Gate::allows('admin'))->load('series.primaryPoster', 'series.folderTags');
                $viewData = $this->preparePreviewData($this->buildFolderPreviewData($category, $folder, $request), $folder->series, $generateRawPreview);
            }

            return response()->view($outputTemplate, $viewData);
        } catch (\Throwable $e) {
            Log::warning('Error generating link preview', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->view('og-preview', $defaultData);
        }
    }

    /**
     * Returns the path to the generated preview image
     *
     * Directly generates on first request and returns exisiting file on subsquent requests
     *
     * If the owner has been edited since the last image was generated, a new one is queued to generate.
     */
    protected function handleGenerateImage(Model $owner, array $data, ?int $dataLastUpdated = 0): ?string {
        $override = config('services.preview_generator.override'); // Forces generation in local environment

        $existingRow = Image::where([
            'imageable_id' => $owner->uuid,
            'imageable_type' => $owner::class,
            'image_type' => ImageType::OGP,
            'replaced_at' => null,
        ])->first();

        $legacyPath = $this->resolveLegacyPath($owner);
        $disk = Storage::disk('public');
        $absoluteLegacyPath = $disk->path($legacyPath);

        $isDbStale = $existingRow && $existingRow->updated_at->timestamp < $dataLastUpdated;
        $isFileStale = ! $existingRow && $dataLastUpdated && file_exists($absoluteLegacyPath) && filemtime($absoluteLegacyPath) < $dataLastUpdated; // Will be false if file does not exist

        // If not override and either db exists and is stale or file exists and is stale -> queue regenerate
        if (! $override && ($isDbStale || $isFileStale)) {
            $this->queueRegeneration($owner, $data);
        }

        if (! $override && $existingRow) {
            return ImageService::getImageUrl($existingRow->path);
        }

        if (! $override && $disk->exists($legacyPath)) {
            return asset('storage/' . $legacyPath);
        }

        $image = $this->generateAndPersist($owner, $data);

        return $image ? ImageService::getImageUrl($image->path) : null;
    }

    /**
     * Generates and persists a preview image for a given owner model
     *
     * !! Writes file to disk !!
     *
     * @param  Model  $owner  Owner model
     * @param  array  $data  Preview data to fill html with
     * @return ?Image persistent image data from database
     */
    public function generateAndPersist(Model $owner, array $data): ?Image {
        try {
            [$relativeOutputPath, $absoluteOutputPath] = $this->imageService->resolvePath($owner, ImageType::OGP, 'png');

            $imageContents = $this->renderImage($data); // raw png data

            if (! $imageContents) {
                throw new GenerateImageException('Browsershot returned no content');
            }

            file_put_contents($absoluteOutputPath, $imageContents);

            if (! file_exists($absoluteOutputPath) || filesize($absoluteOutputPath) === 0) {
                throw new GenerateImageException('Preview image file has no content');
            }

            return $this->imageService->persistImage(
                $owner,
                new ImageData(
                    absolutePath: $absoluteOutputPath,
                    relativePath: $relativeOutputPath,
                    type: ImageType::OGP,
                    source: ImageSource::GENERATED,
                    format: 'png',
                ),
                true
            );
        } catch (\Throwable $th) {
            Log::error('Preview image generation failed', [
                'owner' => $owner::class,
                'id' => $owner->getKey(),
                'error' => $th->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Builds a legacy preview path for an owner, mirroring the previously used pathKeys in handleGenerateImage.
     *
     * Folder (Series): previews/folders/{folder_id}.png
     * Video (Metadata): previews/{folder->path}/{video_id}.png
     *
     * @param  Model  $owner  Database Model to buld a preview path for (Metadata|Series)
     * @return ?string path to preview image for model $owner, null if $owner model is not matched
     */
    protected function resolveLegacyPath(Model $owner): ?string {
        return match (true) {
            $owner instanceof Series => $owner->folder_id ? self::LEGACY_PREVIEW_PREFIX . "folders/{$owner->folder_id}.png" : null,
            $owner instanceof Metadata => ($owner->video?->folder?->path && $owner->video_id) ? self::LEGACY_PREVIEW_PREFIX . "{$owner->video?->folder?->path}/{$owner->video_id}.png" : null,
            default => tap(null, fn () => Log::warning('PreviewGeneratorService: resolveLegacyPath called with unsupported owner', ['owner' => $owner::class, 'id' => $owner->id])),
        };
    }

    protected function buildFolderPreviewData(Category $category, ?Folder $folder, Request $request): array {
        $series = $folder->series;

        $seriesPoster = $series->primaryPoster?->path ? "storage/{$series->primaryPoster->path}" : $series->thumbnail_url;
        $poster = $seriesPoster ?: $this->defaultPoster;

        $isAudio = $folder->isMajorityAudio();
        $fileCount = $series->file_count ?? 0;
        $fileType = ($isAudio ? 'Track' : 'Episode') . ($fileCount === 1 ? '' : 's');
        $contentString = ($series->started_at ? MediaFormatter::getMediaReleaseSeason($series->started_at) . ' • ' : '') . "$fileCount $fileType";
        $studio = ucfirst($series?->studio);

        return [
            'title' => "{$series->title}",
            'description' => $series->description ?: 'No description is available.',
            'studio' => ($studio ? $studio . ' · ' : '') . ucfirst($category->name),
            'is_audio' => $isAudio,
            'file_count' => $fileCount,
            'thumbnail_url' => $this->encodeImageURL($poster),
            'upload_date' => MediaFormatter::formatDate($series->created_at),
            'content_string' => $contentString,
            'rating' => $series->rating,
            'tags' => $series->folder_tags ? array_map(fn ($tag) => $tag->name, $series->folder_tags) : null,
            'url' => $request->fullUrl(),
            'last_updated' => strtotime($series->updated_at ?? ''),
        ];
    }

    protected function buildMediaPreviewData(Metadata $metadata, Request $request) {
        $series = $metadata->video->folder->series;
        $isAudio = $metadata->media_type === MediaType::AUDIO;

        // Poster for music does not auto migrate?
        $metadataPoster = $metadata->primaryPoster?->path ? "storage/{$metadata->primaryPoster->path}" : $metadata->poster_url; // Backwards Compatible
        $seriesPoster = $series->primaryPoster?->path ? "storage/{$series->primaryPoster->path}" : $series->thumbnail_url;
        $poster = $seriesPoster ?: $this->defaultPoster;
        $banner = $metadataPoster ?: $seriesPoster ?: $this->defaultPoster;

        $releaseDate = MediaFormatter::formatDate($metadata->released_at ?: $metadata->file_modified_at);

        return [
            'title' => ucfirst($series->title) . " · {$metadata->title}",
            'description' => $metadata->description ?: $series->description ?: 'No description is available.',
            'thumbnail_url' => $this->encodeImageURL($poster),
            'banner_url' => $this->encodeImageURL($banner),
            'is_audio' => $isAudio,
            'content_string' => $releaseDate . ' • ' . MediaFormatter::formatDuration($metadata?->duration ?? null),
            'release_date' => $releaseDate,
            'upload_date' => MediaFormatter::formatDate($metadata->file_modified_at),
            'mime_type' => $metadata->mime_type,
            'tags' => $metadata->video_tags ? array_map(fn ($tag) => $tag->name, $metadata->video_tags) : [MediaFormatter::formatFileSize($metadata->file_size), "{$metadata->resolution_height}P", strtoupper($metadata->codec)],
            'studio' => ucfirst($series?->studio ?? $metadata->video->folder->category->name),
            'url' => $request->fullUrl(),
            'last_updated' => max(strtotime($series->updated_at), strtotime($metadata->updated_at ?? '') ?: 0),
        ];
    }

    protected function preparePreviewData(array $baseData, Model $owner, bool $generateRawPreview): array {
        $dataLastUpdated = $baseData['last_updated'] ?? 0;
        $baseData['raw'] = $baseData['thumbnail_url'];

        if (! $generateRawPreview && $generatedImage = $this->handleGenerateImage($owner, $baseData, $dataLastUpdated)) {
            $baseData['thumbnail_url'] = $generatedImage;
            $baseData['is_generated'] = true;
        }

        return $baseData;
    }

    // region Rendering / Browsershot

    /**
     * Renders preview image and returns raw png data
     */
    protected function renderImage(array $data): string|false {
        $tempRelativePath = 'cache/render-artifacts/previews/' . uniqid('og-', true) . '.png'; // TODO: use configured cache path
        $tempAbsolutePath = Storage::path($tempRelativePath);
        Storage::makeDirectory(dirname($tempRelativePath));

        try {
            $html = $this->generatePreviewPage($data);

            $browsershot = Browsershot::html($html)
                ->windowSize(1200, 630)
                ->deviceScaleFactor(2)
                ->waitUntilNetworkIdle()
                ->setOption('args', [
                    '--disable-gpu',
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage',
                    '--user-data-dir=' . storage_path('app/chrome'),
                    '--disable-web-security',
                    '--font-render-hinting=none',
                    '--enable-features=NetworkService,NetworkServiceInProcess',
                    '--blink-settings=imagesEnabled=true',
                ])
                ->ignoreConsoleErrors()
                ->setEnvironmentOptions(['CHROME_CONFIG_HOME' => storage_path('app/chrome/.config')]);

            $browsershot = $this->chromiumResolver->setChromiumBinary($browsershot);
            $browsershot->timeout(120)->save($tempAbsolutePath);

            return file_get_contents($tempAbsolutePath);
        } catch (\Throwable $th) {
            $message = $th->getMessage();

            if ($message !== 'The spatie/image package is required to perform image manipulations. Please install it by running `composer require spatie/image`') {
                Log::error('Browsershot render failed', ['error' => $message, 'trace' => $th->getTraceAsString()]);
            }

            return file_exists($tempAbsolutePath) ? file_get_contents($tempAbsolutePath) : false;
        } finally {
            Storage::delete($tempRelativePath);
        }
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

    // endregion

    // region Utility

    protected function defaultData(Request $request): array {
        return [
            'title' => 'Media Server',
            'description' => 'An auto-generated preview for bots.',
            'thumbnail_url' => $this->defaultPoster,
            'url' => $request->fullUrl(),
            'is_audio' => false,
            'mediaUrl' => null,
        ];
    }

    /**
     * Returns an encoded url for a given image
     *
     * Converts to an asset url if the given url is relative in app storage
     *
     * @param  string  $url  Image URL
     * @return string Encoded Image URL
     */
    protected function encodeImageURL(string $url): string {
        if (! filter_var($url, FILTER_VALIDATE_URL) && str_starts_with($url, 'storage/')) {
            $url = asset($url);
        }

        return str_replace([chr(39), chr(34), ' '], ['%27', '%22', '%20'], $url);
    }

    // endregion

    // region Side Effects

    protected function queueRegeneration(Model $owner, array $data): void {
        $alreadyRunning = SubTask::where('reference_uuid', $owner->uuid)
            ->where('reference_type', ImageType::OGP)
            ->whereIn('status', [TaskStatus::PENDING, TaskStatus::PROCESSING])
            ->latest()
            ->first();

        if ($alreadyRunning) {
            return;
        } // Don't duplicate regeneration jobs (maybe use date to determine stale jobs in progress?)

        $fileJobService = app(FileJobService::class);
        $fileJobService->regeneratePreviewImages([['owner' => $owner, 'data' => $data]]);
    }

    // endregion
}

class GenerateImageException extends Exception {}
