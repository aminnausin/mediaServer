<?php

namespace App\Http\Controllers\Api\V1\Server;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Server\ServerConfigService;
use FilesystemIterator;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class DiskUsageController extends Controller {
    private const SIZE_CACHE_TTL = 3600;

    public function __construct(private ServerConfigService $config) {
    }

    public function index(Request $request): JsonResponse {
        $refresh = $request->boolean('refresh');
        $directorySizes = [];
        $filesystems = [];
        $locations = [];

        $isAdmin = Gate::allows('admin');

        foreach ($this->locations($isAdmin) as $location) {
            [$key, $label, $category, $path] = $location;

            $bytes = $location[4] ?? null;

            $real = is_dir($path) ? realpath($path) : false;
            $stat = $real !== false ? stat($real) : false;
            $fsId = $stat !== false ? (string) $stat['dev'] : null;

            if ($fsId !== null && ! isset($filesystems[$fsId])) {
                $total = disk_total_space($real);
                $free = disk_free_space($real);

                if ($total !== false && $free !== false) {
                    $filesystems[$fsId] = [
                        'id' => $fsId,
                        'totalSpace' => $total,
                        'usedSpace' => $total - $free,
                        'freeSpace' => $free,
                    ];
                }
            }

            $locations[] = [
                'key' => $key,
                'label' => $label,
                'category' => $category,
                'path' => $isAdmin ? $path : '/',
                'exists' => (bool) $real,
                'filesystem' => $fsId,
                'bytes' => $category === 'library'
                    ? ($bytes ?? 0)
                    : ($real ? ($directorySizes[$real] ??= $this->directorySize($real, $refresh)) : null),
            ];
        }

        return response()->json([
            'filesystems' => array_values($filesystems),
            'locations' => $locations,
        ]);
    }

    /** @return array<int, array{string, string, string, string, 4?: int|null}> [key, label, category, path, ?bytes] */
    private function locations($hasFullAccess = false): array {
        $private = Storage::disk('local');
        $public = Storage::disk('public');
        $metadataPath = $this->resolve($public, $this->config->get('metadata_path', 'storage/metadata'));
        $cachePath = $this->resolve($private, $this->config->get('cache_path', 'storage/cache'));

        $locations = [
            ['metadata.videos', 'File Thumbnails', 'images', "$metadataPath/metadata"],
            ['metadata.series', 'Folder Thumbnails', 'images', "$metadataPath/series"],
            ['legacy.thumbnails', 'Thumbnails', 'legacy', $this->normalise($public->path('thumbnails'))],
            ['legacy.posters', 'Posters', 'legacy', $this->normalise($public->path('posters'))],
            ['legacy.previews', 'Previews', 'legacy', $this->normalise($public->path('previews'))],
            ['subtitles', 'Subtitles', 'subtitles', $this->normalise($private->path('metadata/media'))],
            ['cache.temporary', 'Temporary', 'cache', "$cachePath/embedding"],
            ['cache.transcripts', 'Transcript Audio', 'cache', "$cachePath/transcripts"],
            ['cache.browser', 'Browser', 'cache', $this->normalise($public->path('app'))],
            ['logs', 'Logs', 'logs', $this->normalise(storage_path('logs'))],
        ];

        $mediaRoot = $this->normalise($public->path('media'));

        $libraries = Category::query()
            ->when(! $hasFullAccess, fn($query) => $query->where('is_private', false))
            ->with('folders.series')
            ->orderBy('name')
            ->get();

        foreach ($libraries as $library) {
            $locations[] = [
                "library.$library->name",
                $library->name,
                'library',
                "$mediaRoot/$library->name",
                $library->folders->sum(
                    fn($folder) => $folder->series?->total_size ?? 0,
                ),
            ];
        }

        return $locations;
    }

    private function resolve(FilesystemAdapter $disk, string $path): string {
        $normalisedPath = $this->normalise($path);
        $isAbsolute = str_starts_with($normalisedPath, '/') || preg_match('#^[A-Za-z]:/#', $normalisedPath);

        return $isAbsolute
            ? $normalisedPath
            : $this->normalise($disk->path(preg_replace('#^storage/#', '', $normalisedPath)));
    }

    private function normalise(string $path): string {
        return str_replace('\\', '/', $path);
    }

    private function directorySize(string $path, bool $refresh): int {
        $key = 'disk-usage.' . md5($path);

        if ($refresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, self::SIZE_CACHE_TTL, function () use ($path) {
            $bytes = 0;
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY,
                RecursiveIteratorIterator::CATCH_GET_CHILD, // skip unreadable
            );

            foreach ($files as $file) {
                if ($file->isFile()) {
                    $bytes += $file->getSize();
                }
            }

            return $bytes;
        });
    }
}
