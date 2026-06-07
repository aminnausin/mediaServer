<?php

namespace App\Services\Images;

use App\Data\Images\ImageData;
use App\Enums\ImageSource;
use App\Enums\ImageType;
use App\Enums\ImageVariant;
use App\Models\Image;
use App\Models\Metadata;
use App\Models\Series;
use Bepsvpt\Blurhash\Facades\BlurHash;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
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

            [$relativeOutputPath, $absoluteOutputPath] = $this->resolvePath($owner, $imageType, $fmt, null, true);

            $response = Http::get($url);

            if (! $response->successful()) {
                Log::warning('Failed to download image', $debugInfo);

                return null;
            }

            Storage::disk('public')->put($relativeOutputPath, $response->body());

            if (! file_exists($absoluteOutputPath) || filesize($absoluteOutputPath) === 0) {
                Log::warning('Failed to download image, file not created', [...$debugInfo, 'outputPath' => $absoluteOutputPath]);

                return null;
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
        } catch (\Throwable $th) {
            Log::warning('Failed to download image', [...$debugInfo, 'error' => $th->getMessage()]);

            return null;
        }
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
            Log::warning('Failed to migrate image', ['path' => $filePath, 'owner' => $owner->class, 'error' => $th->getMessage()]);

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

    public function persistImage(Model $owner, ImageData $data): Image {
        if (! file_exists($data->absolutePath) || filesize($data->absolutePath) === 0) {
            throw new FileNotFoundException("Image file does not exist: {$data->absolutePath}");
        }

        $uuid = $owner->uuid ?? throw new \InvalidArgumentException('Owner must have a UUID');
        [$width, $height] = getimagesize($data->absolutePath);

        Image::where([
            'imageable_id' => $uuid,
            'imageable_type' => $owner::class,
            'image_type' => $data->type,
            'replaced_at' => null,
        ])->update(['replaced_at' => now()]);

        return Image::create([
            'imageable_id' => $uuid,
            'imageable_type' => $owner::class,
            'user_id' => $data->userId,

            'image_type' => $data->type,
            'image_source' => $data->source,
            'image_variant' => $data->variant,

            'width' => $width,
            'height' => $height,
            'size' => filesize($data->absolutePath),

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
}
