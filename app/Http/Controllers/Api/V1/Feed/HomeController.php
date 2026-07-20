<?php

namespace App\Http\Controllers\Api\V1\Feed;

use App\Enums\MediaType;
use App\Http\Controllers\Controller;
use App\Http\Resources\FolderResource;
use App\Http\Resources\VideoResource;
use App\Models\Category;
use App\Models\Folder;
use App\Models\Metadata;
use App\Models\PlaybackProgress;
use App\Models\Series;
use App\Models\Video;
use Illuminate\Http\Request;

class HomeController extends Controller {
    protected int $defaultLimit = 20;

    public function continueWatching(Request $request) {
        $libraryIds = Category::visibleTo($request->user())->pluck('id');

        $progressEntries = PlaybackProgress::query()
            ->forCurrentIdentity()
            ->where('progress_percentage', '<', 100)
            ->with(['metadata.video.folder'])
            ->whereHas('metadata.video.folder', fn ($q) => $q->whereIn('category_id', $libraryIds))
            ->orderByDesc('updated_at')
            ->limit($this->defaultLimit)->get();

        $videos = $progressEntries->map(function (PlaybackProgress $progress) {
            return $this->eagerLoadVideo($progress->metadata->video, $progress->metadata);
        });

        return VideoResource::collection($videos);
    }

    public function recentlyAdded(Request $request) {
        $libraryIds = Category::visibleTo($request->user())->pluck('id');

        $series = Series::query()
            ->whereHas('folder', function ($query) use ($libraryIds) {
                $query->whereIn('category_id', $libraryIds);
            })
            ->with(['folder'])
            ->where('file_count', '>', 0)
            ->orderByDesc('created_at')
            ->limit($this->defaultLimit)
            ->get();

        $folders = $series->map(function (Series $series) {
            return $this->eagerLoadFolder($series);
        });

        return FolderResource::collection($folders);
    }

    public function recentlyUpdated(Request $request) {
        $libraryIds = Category::visibleTo($request->user())->pluck('id');

        $series = Series::query()
            ->whereHas('folder', function ($query) use ($libraryIds) {
                $query->whereIn('category_id', $libraryIds);
            })
            ->with(['folder'])
            ->where('file_count', '>', 0)
            ->withMax('videos', 'created_at')
            ->orderByDesc('videos_max_created_at')
            ->limit($this->defaultLimit)
            ->get();

        $folders = $series->map(function (Series $series) {
            return $this->eagerLoadFolder($series);
        });

        return FolderResource::collection($folders);
    }

    public function recentlyReleased(Request $request) {
        $libraryIds = Category::visibleTo($request->user())->pluck('id');

        $series = Series::query()
            ->whereHas('folder', function ($query) use ($libraryIds) {
                $query->whereIn('category_id', $libraryIds);
            })
            ->with(['folder'])
            ->whereNotNull('started_at')
            ->orderByDesc('started_at')
            ->limit($this->defaultLimit)
            ->get();

        $folders = $series->map(function (Series $series) {
            return $this->eagerLoadFolder($series);
        });

        return FolderResource::collection($folders);
    }

    public function recentlyUploaded(Request $request) {
        $libraryIds = Category::visibleTo($request->user())->pluck('id');
        $typeParam = $request->query('type');

        $mediaType = collect(MediaType::cases())->first(fn (MediaType $case) => $case->label() === strtolower((string) $typeParam));

        $videos = Video::query()
            ->with(['folder', 'metadata'])
            ->whereHas('folder', function ($query) use ($libraryIds, $mediaType) {
                $query->whereIn('category_id', $libraryIds);
                if ($mediaType !== null) {
                    $query->whereHas('series', function ($seriesQuery) use ($mediaType) {
                        $seriesQuery->where('primary_media_type', $mediaType);
                    });
                }
            })
            ->orderByDesc('created_at')
            ->limit($this->defaultLimit)
            ->get();

        $videos = $videos->map(function (Video $video) {
            return $this->eagerLoadVideo($video, $video->metadata);
        });

        return VideoResource::collection($videos);
    }

    public function recentlyUploadedMusic(Request $request) {
        $libraryIds = Category::visibleTo($request->user())->pluck('id');

        $videos = Video::query()
            ->with(['folder', 'metadata'])
            ->whereHas('folder', function ($query) use ($libraryIds) {
                $query->whereIn('category_id', $libraryIds);
                $query->where('primary_media_type', '=', MediaType::AUDIO);
            })
            ->orderByDesc('created_at')
            ->limit($this->defaultLimit)
            ->get();

        $videos = $videos->map(function (Video $video) {
            return $this->eagerLoadVideo($video, $video->metadata);
        });

        return VideoResource::collection($videos);
    }

    private function eagerLoadVideo(Video $video, Metadata $metadata): Video {
        $metadata->load([
            'storyboard',
            'primaryPoster',
        ]);

        $video->setRelation('metadata', $metadata);

        return $video;
    }

    private function eagerLoadFolder(Series $series): Folder {
        $folder = $series->folder;
        $series->load([
            'primaryPoster',
            'primaryBanner',
        ]);

        $folder->setRelation('series', $series);

        return $folder;
    }
}
