<?php

namespace App\Http\Controllers\Api\V1\Feed;

use App\Enums\MediaType;
use App\Http\Controllers\Controller;
use App\Http\Resources\FolderResource;
use App\Http\Resources\VideoResource;
use App\Models\Category;
use App\Models\Folder;
use App\Models\PlaybackProgress;
use App\Models\Series;
use App\Models\Video;
use App\Services\Auth\GuestIdentity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller {
    protected int $defaultLimit = 20;

    public function continueWatching(Request $request) {
        $libraryIds = $this->visibleLibraryIds($request);

        $progressEntries = GuestIdentity::scope(PlaybackProgress::query())
            ->select('playback_progress.*')
            ->join('metadata', 'metadata.id', '=', 'playback_progress.metadata_id')
            ->join('videos', 'videos.id', '=', 'metadata.video_id')
            ->join('folders', 'folders.id', '=', 'videos.folder_id')
            ->where('playback_progress.progress_percentage', '<', 100)
            ->whereIn('folders.category_id', $libraryIds)
            ->orderByDesc('playback_progress.updated_at')
            ->limit($this->defaultLimit)
            ->with([
                'metadata.video.folder',
                'metadata.storyboard',
                'metadata.primaryPoster',
            ])
            ->get();

        $videos = $progressEntries->map(function (PlaybackProgress $progress) {
            $metadata = $progress->metadata;
            $video = $metadata->video;

            $metadata->setRelation('playbackProgress', $progress);
            $video->setRelation('metadata', $metadata);

            return $video;
        });

        return VideoResource::collection($videos);
    }

    public function recentlyAdded(Request $request) {
        $series = $this->seriesFeedQuery($this->visibleLibraryIds($request))
            ->orderByDesc('created_at')
            ->limit($this->defaultLimit)
            ->get();

        return FolderResource::collection($this->mapSeriesToFolders($series));
    }

    public function recentlyUpdated(Request $request) {
        $series = $this->seriesFeedQuery($this->visibleLibraryIds($request))
            ->withMax('videos', 'created_at')
            ->orderByDesc('videos_max_created_at')
            ->orderByDesc('updated_at')
            ->limit($this->defaultLimit)
            ->get();

        return FolderResource::collection($this->mapSeriesToFolders($series));
    }

    public function recentlyReleased(Request $request) {
        $series = $this->seriesFeedQuery($this->visibleLibraryIds($request))
            ->whereNotNull('started_at')
            ->orderByDesc('started_at')
            ->limit($this->defaultLimit)
            ->get();

        return FolderResource::collection($this->mapSeriesToFolders($series));
    }

    public function recentlyUploaded(Request $request) {
        $mediaType = MediaType::fromLabel($request->query('type'));

        $videos = $this->videoFeedQuery($this->visibleLibraryIds($request))
            ->when($mediaType, fn ($q) => $q->where('series.primary_media_type', $mediaType))
            ->orderByDesc('metadata.created_at')
            ->limit($this->defaultLimit)
            ->get();

        return VideoResource::collection($videos);
    }

    private function seriesFeedQuery(Collection $libraryIds): Builder {
        return Series::query()
            ->whereHas(
                'folder',
                fn ($q) => $q->whereIn('category_id', $libraryIds)
            )
            ->where('file_count', '>', 0)
            ->with([
                'folder',
                'primaryPoster',
                'primaryBanner',
            ]);
    }

    private function videoFeedQuery(Collection $libraryIds): Builder {
        return Video::query()
            ->select('videos.*')
            ->join('metadata', 'metadata.video_id', '=', 'videos.id')
            ->join('folders', 'folders.id', '=', 'videos.folder_id')
            ->leftJoin('series', 'series.folder_id', '=', 'folders.id')
            ->whereIn('folders.category_id', $libraryIds)
            ->whereNotNull('metadata.created_at')
            ->with([
                'folder',
                'metadata.storyboard',
                'metadata.primaryPoster',
                'metadata.playbackProgress',
            ]);
    }

    private function mapSeriesToFolders(Collection $series): Collection {
        return $series->map(function (Series $series): Folder {
            $folder = $series->folder;
            $folder->setRelation('series', $series);

            return $folder;
        });
    }

    private function visibleLibraryIds(Request $request): Collection {
        $user = $request->user();

        $cacheKey = $user ? "{$user->id}:visible_libraries" : 'public:visible_libraries';

        return Cache::tags(['library-visibility'])->remember($cacheKey, now()->addSeconds(60), fn () => Category::visibleTo($user)->pluck('id'));
    }
}
