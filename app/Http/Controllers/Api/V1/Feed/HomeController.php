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

class HomeController extends Controller {
    protected int $defaultLimit = 20;

    public function continueWatching(Request $request) {
        $libraryIds = $this->visibleLibraryIds($request);

        $progressEntries = GuestIdentity::scope(PlaybackProgress::query())
            ->where('progress_percentage', '<', 100)
            ->with([
                'metadata.video.folder',
                'metadata.storyboard',
                'metadata.primaryPoster',
            ])
            ->whereHas('metadata.video.folder', fn ($q) => $q->whereIn('category_id', $libraryIds))
            ->orderByDesc('updated_at')
            ->limit($this->defaultLimit)->get();

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
            ->when($mediaType, function ($query) use ($mediaType) {
                $query->whereHas('folder.series', function ($query) use ($mediaType) {
                    $query->where('primary_media_type', $mediaType);
                });
            })
            ->orderByDesc('created_at')
            ->limit($this->defaultLimit)
            ->get();

        return VideoResource::collection($videos);
    }

    public function recentlyUploadedMusic(Request $request) {
        $videos = $this->videoFeedQuery($this->visibleLibraryIds($request))
            ->whereHas(
                'folder',
                fn ($q) => $q->where('primary_media_type', MediaType::AUDIO)
            )
            ->orderByDesc('created_at')
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
            ->with([
                'folder',
                'metadata.storyboard',
                'metadata.primaryPoster',
                'metadata.playbackProgress',
            ])
            ->whereHas(
                'folder',
                fn ($q) => $q->whereIn('category_id', $libraryIds)
            );
    }

    private function mapSeriesToFolders(Collection $series): Collection {
        return $series->map(function (Series $series): Folder {
            $folder = $series->folder;
            $folder->setRelation('series', $series);

            return $folder;
        });
    }

    private function visibleLibraryIds(Request $request): Collection {
        return Category::visibleTo($request->user())->pluck('id');
    }
}
