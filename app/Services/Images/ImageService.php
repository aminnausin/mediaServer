<?php

namespace App\Services\Images;

use App\Data\Images\ImageData;
use App\Data\Images\ImageUpdateData;
use App\Enums\ImageSource;
use App\Enums\ImageType;
use App\Enums\ImageVariant;
use App\Exceptions\Images\InvalidImageDataException;
use App\Models\Image;
use App\Models\Metadata;
use App\Models\Series;
use Bepsvpt\Blurhash\Facades\BlurHash;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ImageService {
    /**
     * Extracts poster from a video file and saves to metadata directory
     *
     * @param  string  $filePath  Absolute path to the input file
     * @return ?Image returns Image row
     */
    public function generateVideoPoster(string $filePath, Metadata $metadata, ?int $userId = null): ?Image {
        $offset = min(30, (int) ($metadata->duration * 0.1)); // 10% in or 30s min
        $fmt = 'webp';

        [$relativeOutputPath, $absoluteOutputPath] = $this->resolvePath($metadata, ImageType::POSTER, $fmt, null, true);

        $process = new Process([
            'ffmpeg',
            '-hwaccel',
            'auto',
            '-ss',
            $offset,
            '-i',
            $filePath,
            '-frames:v',
            '1',
            '-c:v',
            'libwebp',
            '-preset',
            'photo',
            '-quality',
            '85',
            '-threads',
            '1',
            '-y',
            $absoluteOutputPath,
        ]);
        try {
            $process->mustRun();

            Log::info("Generated poster for {$metadata->composite_id}", [
                'uuid' => $metadata->uuid,
                'file' => $filePath,
                'poster' => $absoluteOutputPath,
            ]);

            return $this->persistImage(
                $metadata,
                new ImageData(
                    absolutePath: $absoluteOutputPath,
                    relativePath: $relativeOutputPath,
                    type: ImageType::POSTER,
                    source: ImageSource::GENERATED,
                    format: $fmt,
                    userId: $userId
                )
            );
        } catch (\Throwable $th) {
            Log::error(
                "Video poster generation failed for {$metadata->uuid}",
                ['file' => $filePath, 'error' => $th->getMessage(), 'command' => $process->getCommandLine()]
            );

            return null;
        }
    }

    /**
     * Extracts embedded poster (album art) from a file and saves to metadata directory
     *
     * @param  string  $filePath  Absolute path to the input file
     * @return ?Image returns Image row
     */
    public function extractPoster(string $filePath, Metadata $metadata, ?int $userId = null): ?Image {
        $fmt = 'webp'; // Audio supports png only ?

        [$relativeOutputPath, $absoluteOutputPath] = $this->resolvePath($metadata, ImageType::POSTER, $fmt, null, true);

        $process = new Process([
            'ffmpeg',
            '-i',
            $filePath,
            '-an',
            '-vcodec',
            'copy',
            '-f',
            'image2',
            '-update',
            '1',
            '-y',
            $absoluteOutputPath,
        ]);

        try {
            $process->run();

            if (! $process->isSuccessful() || ! file_exists($absoluteOutputPath) || filesize($absoluteOutputPath) === 0) {
                // Checks if error is caused by missing album art (never set so dont log)
                if (str_contains($process->getErrorOutput(), 'Output file does not contain any stream')) {
                    return null;
                }
                throw new ProcessFailedException($process);
            }

            Log::info("Extracted poster from {$metadata->composite_id} for {$metadata->uuid}", [
                'uuid' => $metadata->uuid,
                'file' => $filePath,
                'poster' => $absoluteOutputPath,
            ]);

            return $this->persistImage(
                $metadata,
                new ImageData(
                    absolutePath: $absoluteOutputPath,
                    relativePath: $relativeOutputPath,
                    type: ImageType::POSTER,
                    source: ImageSource::EMBEDDED,
                    format: $fmt,
                    userId: $userId
                )
            );
        } catch (\Throwable $th) {
            Log::error(
                "Poster extraction failed for {$metadata->uuid}",
                ['file' => $filePath, 'error' => $th->getMessage(), 'command' => $process->getCommandLine()]
            );

            return null;
        }
    }

    public function downloadFromUrl(string $url, Model $owner, ImageType $imageType, ?int $userId = null): ?Image {
        $debugInfo = ['url' => $url, 'owner' => $owner::class, 'id' => $owner->id, 'uuid' => $owner->uuid ?? $owner->getKey()];

        try {
            $fmt = 'webp';

            $url = $this->validateUrl($url, $debugInfo);
            if (! $url) {
                return null;
            }

            [$relativeOutputPath, $absoluteOutputPath] = $this->resolvePath($owner, $imageType, $fmt, null, true);

            $response = Http::timeout(10)->connectTimeout(5)->retry(1, 250)->maxRedirects(3)->accept('image/*')->get($url);

            if (! $response->successful()) {
                throw new InvalidImageDataException('Failed to download image');
            }

            $imageData = $this->validateDownloadedImage($response, $debugInfo);

            if (! $imageData) {
                return null;
            }

            $webpData = $this->convertToWebp($imageData);
            if (! $webpData) {
                throw new InvalidImageDataException('Failed to convert downloaded image to webp');
            }

            Storage::disk('public')->put($relativeOutputPath, $webpData);

            if (! file_exists($absoluteOutputPath) || filesize($absoluteOutputPath) === 0) {
                throw new InvalidImageDataException('Failed to download image, file not created', ['outputPath' => $absoluteOutputPath]);
            }

            return $this->persistImage(
                $owner,
                new ImageData(
                    absolutePath: $absoluteOutputPath,
                    relativePath: $relativeOutputPath,
                    type: $imageType,
                    source: ImageSource::DOWNLOADED,
                    format: $fmt,
                    userId: $userId,
                    sourceUrl: $url
                )
            );
        } catch (InvalidImageDataException $th) {
            Log::warning($th->getMessage(), [
                ...$debugInfo,
                ...$th->getContext(),
            ]);

            return null;
        } catch (\Throwable $th) {
            Log::warning('Failed to download image', [...$debugInfo, 'error' => $th->getMessage()]);

            return null;
        }
    }

    /**
     * Upload image file to disk and save to db
     *
     * Only accepts jpeg, png and webp mime types
     *
     * @return Image persisted image data from disk
     */
    public function uploadImage(UploadedFile $file, Model $owner, ImageType $imageType, int $userId): ?Image {
        $debugInfo = ['owner' => $owner::class, 'id' => $owner->id, 'uuid' => $owner->uuid ?? $owner->getKey(), 'image_type' => $imageType->value];

        try {
            if (! $file->isValid()) {
                Log::warning(
                    'Uploaded image invalid',
                    $debugInfo
                );

                return null;
            }

            $fmt = match ($file->getMimeType()) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => throw new \InvalidArgumentException("Unsupported image format: {$file->getMimeType()}"),
            };

            [$relativeOutputPath, $absoluteOutputPath] = $this->resolvePath($owner, $imageType, $fmt, null, true);

            Storage::disk('public')->putFileAs(dirname($relativeOutputPath), $file, basename($relativeOutputPath));

            if (! file_exists($absoluteOutputPath) || filesize($absoluteOutputPath) === 0) {
                Log::warning('Failed to upload image, file missing', [...$debugInfo, 'outputPath' => $absoluteOutputPath]);

                return null;
            }

            return $this->persistImage(
                $owner,
                new ImageData(
                    absolutePath: $absoluteOutputPath,
                    relativePath: $relativeOutputPath,
                    type: $imageType,
                    source: ImageSource::UPLOADED,
                    format: $fmt,
                    userId: $userId,
                )
            );
        } catch (\Throwable $th) {
            Log::warning('Failed to upload image', [...$debugInfo, 'error' => $th->getMessage()]);

            return null;
        }
    }

    public function resolveUpdatedImage(Model $owner, ImageUpdateData $data): ?Image {
        $image = match ($data->mode) {
            'existing' => $owner->images()->where('id', $data->imageId)->where('image_type', $data->imageType)->firstOrFail(),
            'upload' => $this->uploadImage($data->file, $owner, $data->imageType, $data->user->id),
            'url' => $this->downloadFromUrl($data->url, $owner, $data->imageType, $data->user->id),
            'remove' => null,
            default => throw new \InvalidArgumentException("Unknown mode: {$data->mode}"),
        };

        return $image;
    }

    /**
     * Sets replaced_at to now on the given images by id.
     *
     * Only allows 'deleting' uploaded files and images downloaded from a url that are owned by the requesting user
     *
     * @param  Model  $owner  model to delete images from
     * @param  ImageUpdateData  $data  DTO that holds delete request information
     */
    public function softDeleteImages(Model $owner, ImageUpdateData $data): void {
        if (! $data->deletedIds) {
            return;
        }

        $affected = $owner->images()
            ->whereIn('id', $data->deletedIds)
            ->whereIn('image_source', [ImageSource::UPLOADED->value, ImageSource::DOWNLOADED->value])
            ->whereNull('replaced_at')
            ->get(['id', 'path', 'image_type', 'image_source', 'user_id']);

        if (! $data->isAdmin && $affected->where('user_id', '!=', $data->user->id)->isNotEmpty()) {
            throw new AuthorizationException('You do not own one or more of the images you are trying to delete.');
        }

        if ($affected->isEmpty()) {
            return;
        }

        $affected->each->update(['replaced_at' => now()]);

        $uuid = $owner->uuid ?? $owner->getKey();
        $username = $data->user->email ?? "user {$data->user->id}";

        Log::info("Images soft deleted by {$username} on " . class_basename($owner) . " {$uuid}", [
            'owner_id' => $owner->getKey(),
            'deleted_by' => $data->user->id,
            'images' => $affected->map(fn($i) => [
                'id' => $i->id,
                'path' => $i->path,
                'type' => $i->image_type,
                'source' => $i->image_source,
            ])->toArray(),
            'requested_ids' => $data->deletedIds,
            'skipped' => count($data->deletedIds) - $affected->count(),
        ]);
    }

    // Untested
    // Dangerous
    public function migrateImage(string $filePath, Model $owner, ImageType $imageType, ?int $userId = null): ?Image {
        try {
            $fmt = pathinfo($filePath, PATHINFO_EXTENSION);

            [$relativeOutputPath, $absoluteOutputPath] = $this->resolvePath($owner, $imageType, $fmt);

            if (! file_exists($filePath) || filesize($filePath) === 0) {
                throw new \InvalidArgumentException('File provided for migration is invalid');
            }

            rename($filePath, $absoluteOutputPath);

            return $this->persistImage(
                $owner,
                new ImageData(
                    absolutePath: $absoluteOutputPath,
                    relativePath: $relativeOutputPath,
                    type: $imageType,
                    source: ImageSource::LEGACY,
                    format: $fmt,
                    userId: $userId,
                )
            );
        } catch (\Throwable $th) {
            Log::warning('Failed to migrate image', ['path' => $filePath, 'owner' => $owner::class, 'error' => $th->getMessage()]);

            return null;
        }
    }

    private function generateBlurHash(string $absolutePath): ?string {
        try {
            return BlurHash::encode($absolutePath);
        } catch (\Throwable $th) {
            Log::error('Blur hash generation failed', ['file' => $absolutePath, 'error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);

            return null;
        }
    }

    /**
     * Resolves relative and absolute path for an image type, format and variant for an imageable model
     *
     * Path: metadata/{imageable}/{shard}/{uuid}/{type->label}.{format}
     * Disk: public
     */
    public function resolvePath(Model $owner, ImageType $type, string $format, ?ImageVariant $variant = null, bool $useHash = false): array {
        $uuid = $owner->uuid ?? $owner->getKey();

        // TODO: This really needs to go outside of this file. "metadata/metadata" is defined all over the place and needs to be central and consistent
        $prefix = match (true) {
            $owner instanceof Metadata => 'metadata',
            $owner instanceof Series => 'series',
            default => throw new \UnexpectedValueException('Unknown imageable: ' . $owner::class),
        };

        $disk = Storage::disk('public');
        $relativeDir = "metadata/{$prefix}/" . substr($uuid, 0, 2) . "/{$uuid}";
        $disk->makeDirectory($relativeDir);

        $hash = $useHash ? '_' . substr(str_replace('-', '', (string) Str::uuid()), 0, 8) : '';
        $filename = $type->value . ($variant ? "_{$variant->value}" : '') . "{$hash}.{$format}";

        $relativePath = "{$relativeDir}/{$filename}";
        $absolutePath = str_replace('\\', '/', $disk->path($relativePath));

        return [$relativePath, $absolutePath];
    }

    public function persistImage(Model $owner, ImageData $data, bool $overwrite = false, bool $forceReplace = false): Image {
        if (! file_exists($data->absolutePath) || filesize($data->absolutePath) === 0) {
            throw new FileNotFoundException("Image file does not exist: {$data->absolutePath}");
        }

        $uuid = $owner->uuid ?? throw new \InvalidArgumentException('Owner must have a UUID');
        $fileSize = filesize($data->absolutePath);
        [$width, $height] = getimagesize($data->absolutePath);

        if ($overwrite) {
            return Image::updateOrCreate(
                [
                    'imageable_id' => $uuid,
                    'imageable_type' => $owner::class,
                    'image_type' => $data->type,
                    'replaced_at' => null,
                ],
                [
                    'user_id' => $data->userId,

                    'image_source' => $data->source,
                    'image_variant' => $data->variant,

                    'width' => $width,
                    'height' => $height,
                    'size' => $fileSize,

                    'path' => $data->relativePath,
                    'format' => $data->format,
                    'source_url' => $data->sourceUrl,

                    'blur_hash' => $this->generateBlurHash($data->absolutePath),
                ]
            );
        }

        // Only replace generated / extracted / auto downloaded files
        // User uploaded / url downloaded images should not be replaced?
        $autoSources = [ImageSource::GENERATED, ImageSource::EMBEDDED];

        if (in_array($data->source, $autoSources) || $forceReplace) {
            Image::where([
                'imageable_id' => $uuid,
                'imageable_type' => $owner::class,
                'image_type' => $data->type,
                'replaced_at' => null,
            ])->update(['replaced_at' => now()]);
        }

        return Image::create([
            'imageable_id' => $uuid,
            'imageable_type' => $owner::class,
            'user_id' => $data->userId,

            'image_type' => $data->type,
            'image_source' => $data->source,
            'image_variant' => $data->variant,

            'width' => $width,
            'height' => $height,
            'size' => $fileSize,

            'path' => $data->relativePath,
            'format' => $data->format,
            'source_url' => $data->sourceUrl,

            'blur_hash' => $this->generateBlurHash($data->absolutePath),
        ]);
    }

    public static function getImageUrl(string $relativePath, string $disk = 'public') {
        /**
         * @disregard P1013 Undefined method but it actually exists
         */
        return Storage::disk($disk)->url($relativePath);
    }

    protected function validateUrl(string $url, array $debugInfo): ?string {
        try {
            $scheme = strtolower(parse_url(trim($url), PHP_URL_SCHEME) ?? '');
            if (! in_array($scheme, ['http', 'https'], true)) {
                throw new InvalidImageDataException('Invalid URL scheme', ['scheme' => $scheme]);
            }

            $host = strtolower(parse_url(trim($url), PHP_URL_HOST) ?? '');
            if (! $host) {
                throw new InvalidImageDataException('Missing URL host');
            }

            if (config('media.image_downloads.security.allow_private_network_urls')) {
                return $url;
            }

            if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
                throw new InvalidImageDataException('Blocked localhost image URL');
            }

            // Filters raw ip addresses that are not public
            if (filter_var($host, FILTER_VALIDATE_IP) && ! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new InvalidImageDataException('Blocked private image URL', ['host' => $host]);
            }

            return $url;
        } catch (InvalidImageDataException $th) {
            Log::warning($th->getMessage(), [
                ...$debugInfo,
                ...$th->getContext(),
            ]);

            return null;
        }
    }

    protected function validateDownloadedImage(Response $response, array $debugInfo): ?string {
        try {
            $contentType = strtolower(explode(';', $response->header('content-type'))[0]);

            // Filter mime/content type
            if (! str_starts_with($contentType, 'image/')) {
                throw new InvalidImageDataException('Downloaded file is not an image', ['content_type' => $contentType]);
            }

            $body = $response->body();
            $maxBytes = config('media.image_downloads.max_size_kb', 10240) * 1024;

            // Filter against max size
            if (strlen($body) > $maxBytes) {
                throw new InvalidImageDataException('Downloaded image exceeds size limit', ['size_bytes' => strlen($body)]);
            }

            // Filter image bytes
            if (! @getimagesizefromstring($body)) {
                throw new InvalidImageDataException('Downloaded content is not a valid image');
            }

            return $body;
        } catch (InvalidImageDataException $th) {
            Log::warning($th->getMessage(), [
                ...$debugInfo,
                ...$th->getContext(),
            ]);

            return null;
        }
    }

    protected function convertToWebp(string $imageData): ?string {
        $source = @imagecreatefromstring($imageData);
        if (! $source) {
            return null;
        }

        ob_start();
        imagewebp($source, null, 85);

        return ob_get_clean() ?: null;
    }
}
