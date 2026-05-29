<?php

namespace App\Services\Images;

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
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

readonly class ImageData {
    public function __construct(
        public string $absolutePath,
        public string $relativePath,
        public ImageType $type,
        public ImageSource $source,
        public string $format,
        public ?ImageVariant $variant = null,
        public ?int $userId = null,
    ) {}
}

class ImageService {
    /**
     * Extracts poster from a video file and saves to metadata directory
     *
     * @param  string  $filePath  Absolute path to the input file
     * @return ?Image returns Image row
     */
    public function generateVideoPoster(string $filePath, Metadata $metadata, ?int $userId = null): ?Image {
        $offset = max(30, (int) ($metadata->duration * 0.1)); // 10% in or 30s min
        $fmt = 'webp';

        [$relativePath, $absolutePath] = $this->resolvePath($metadata, ImageType::POSTER, $fmt);

        try {
            $process = new Process([
                'ffmpeg',
                '-ss',
                $offset,
                '-i',
                $filePath,
                '-frames:v',
                '1',
                '-c:v',
                'libwebp',
                '-quality',
                '85',
                '-y',
                $absolutePath,
            ]);
            $process->mustRun();

            return $this->persistImage($metadata, new ImageData(absolutePath: $absolutePath, relativePath: $relativePath, type: ImageType::POSTER, source: ImageSource::GENERATED, format: $fmt, userId: $userId));
        } catch (\Throwable $th) {
            Log::error('Video poster generation failed', ['file' => $filePath, 'error' => $th->getMessage()]);

            return null;
        }
    }

    /**
     * Extracts album art from a file and saves to metadata directory
     *
     * @param  string  $filePath  Absolute path to the input file
     * @return ?Image returns Image row
     */
    public function extractAlbumArt(string $filePath, Metadata $metadata, bool $overwrite = false, ?int $userId = null): ?Image {
        $fmt = 'webp'; // Audio supports png only ?

        [$relativePath, $absolutePath] = $this->resolvePath($metadata, ImageType::POSTER, $fmt);

        try {
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
                $absolutePath,
            ]);
            $process->run();

            if (! $process->isSuccessful()) {
                // Checks if error is caused by missing album art (never set so dont log)
                if (str_contains($process->getErrorOutput(), 'Output file does not contain any stream')) {
                    return null;
                }
                throw new ProcessFailedException($process);
            }

            return $this->persistImage($metadata, new ImageData(absolutePath: $absolutePath, relativePath: $relativePath, type: ImageType::POSTER, source: ImageSource::EMBEDDED, format: $fmt, userId: $userId));
        } catch (\Throwable $th) {
            Log::error('Album art extraction failed', ['file' => $filePath, 'error' => $th->getMessage()]);

            return null;
        }
    }

    public function downloadFromUrl(string $url, Model $owner, ImageType $imageType, ?int $userId = null): ?Image {
        try {
            $fmt = 'webp';

            [$relativePath, $absolutePath] = $this->resolvePath($owner, $imageType, $fmt);

            $response = Http::get($url);
            if (! $response->successful()) {
                return null;
            }

            Storage::disk('public')->put($relativePath, $response->body());

            return $this->persistImage($owner, new ImageData(absolutePath: $absolutePath, relativePath: $relativePath, type: $imageType, source: ImageSource::DOWNLOADED, format: $fmt, userId: $userId));
        } catch (\Throwable $th) {
            Log::warning('Failed to download image', ['url' => $url, 'error' => $th->getMessage()]);

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

    private function resolvePath(Model $owner, ImageType $type, string $format, ?ImageVariant $variant = null): array {
        $uuid = $owner->uuid ?? $owner->getKey();
        $prefix = match (true) {
            $owner instanceof Metadata => 'metadata',
            $owner instanceof Series => 'series',
            default => 'assets',
        };

        $disk = Storage::disk('public');
        $relativeDir = "images/{$prefix}/" . substr($uuid, 0, 2) . "/{$uuid}";
        $disk->makeDirectory($relativeDir);

        $filename = $type->label() . ($variant ? "_{$variant->value}" : '') . ".{$format}";

        $relativePath = "{$relativeDir}/{$filename}";
        $absolutePath = str_replace('\\', '/', $disk->path($relativePath));

        return [$relativePath, $absolutePath];
    }

    private function persistImage(Model $owner, ImageData $data): Image {
        if (! file_exists($data->absolutePath) || filesize($data->absolutePath) === 0) {
            throw new FileNotFoundException("Image file not created: {$data->absolutePath}");
        }

        $uuid = $owner->uuid ?? throw new \InvalidArgumentException('Owner must have a UUID');

        Image::where([
            'imageable_id' => $uuid,
            'imageable_type' => $owner::class,
            'image_type' => $data->type,
            'replaced_at' => null,
        ])->update(['replaced_at' => now()]);

        [$width, $height] = getimagesize($data->absolutePath);

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

            'blur_hash' => $this->generateBlurhash($data->absolutePath),
        ]);
    }

    public static function getImageUrl(string $relativePath, string $disk = 'public') {
        /**
         * @disregard P1013 Undefined method but it actually exists
         */
        return Storage::disk($disk)->url($relativePath);
    }
}
