<?php

namespace App\Jobs;

use App\Enums\MediaType;
use App\Enums\TaskStatus;
use App\Models\Metadata;
use App\Models\Record;
use App\Models\SubTask;
use App\Services\TaskService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class VerifyFiles extends ManagedTaskJob {

    /**
     * Execute the job.
     *  id NOT_NULL      -> INT8
     *  video_id         -> INT8
     *  composite_id     -> VARCHAR
     *  title            -> VARCHAR
     *  season           -> INT4
     *  episode          -> INT4
     *  duration         -> INT4
     *  view_count       -> INT4
     *  description      -> VARCHAR
     *  date_released    -> DATE
     *  editor_id        -> INT8
     *  created_at       -> DATE
     *  updated_at       -> DATE
     *  uuid             -> uuid
     *  file_size        -> INT8
     *  date_scanned     -> INT8
     *  artist           -> VARCHAR
     *  album            -> VARCHAR
     */

    protected $embedChain = [];

    protected $fileMetaData = [];

    /**
     * Create a new job instance.
     */
    public function __construct(public $videos, int $taskId) {
        $subTask = SubTask::create(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => 'Verify ' . count($videos) . ' Files']);

        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    public function handle(TaskService $taskService): void {
        $this->beginTask($taskService);

        try {
            $summary = $this->verifyFiles($taskService);
            $endedAt = now();
            $duration = (int) $this->startedAt->diffInSeconds($endedAt);

            if (count($this->embedChain)) {
                $taskService->updateTaskCounts($this->taskId, ['sub_tasks_complete' => '++', 'sub_tasks_total' => count($this->embedChain), 'sub_tasks_pending' => count($this->embedChain)]);
                foreach ($this->embedChain as $embedTask) {
                    Bus::dispatch($embedTask);
                }
            } else {
                $taskService->updateTaskCounts($this->taskId, ['sub_tasks_complete' => '++'], false);
            }

            $taskService->updateSubTask($this->subTaskId, [
                'status' => TaskStatus::COMPLETED,
                'summary' => $summary,
                'progress' => 100,
                'ended_at' => $endedAt,
                'duration' => $duration,
            ]);
        } catch (\Throwable $th) {
            $endedAt = now();
            $duration = (int) $this->startedAt->diffInSeconds($endedAt);
            $taskService->updateTaskCounts($this->taskId, ['sub_tasks_failed' => '++']);
            $taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::FAILED, 'summary' => 'Error: ' . $th->getMessage(), 'ended_at' => $endedAt, 'duration' => $duration]);
            throw $th;
        }
    }

    private function verifyFiles(TaskService $taskService) {
        $transactions = [];
        $error = false;
        $index = 0;

        if (count($this->videos) == 0) {
            throw new \Exception('Video Data Lost');
        }

        try {
            foreach ($this->videos as $video) {
                $stored = []; // Metadata from db
                $changes = []; // Changes -> stored + changes . length has to be the same for every video so must generate defaults

                $compositeId = $video->folder->path . '/' . basename($video->path);
                $filePath = str_replace('\\', '/', Storage::disk('public')->path('')) . 'media/' . $video->folder->path . '/' . basename($video->path);

                /**
                 * @disregard P1013 Undefined method but it actually exists
                 */
                if (! Storage::disk('public')->fileExists('media/' . $video->folder->path . '/' . basename($video->path))) {
                    throw new \Exception('Video "media/' . $video->folder->path . '/' . basename($video->path) . '" no longer exists. Index your videos before running this task again.');
                }

                // For use with private storage -> $filePath = str_replace('\\', '/', Storage::path('app/private/')) . 'media/' . $video->folder->path . "/" . basename($video->path);
                $this->fileMetaData = is_null($video->uuid) ? $this->getFileMetadata($filePath) : []; // Empty unless uuid is missing or duration is missing
                $uuid = $video->uuid ?? ''; // video has ? file has

                // if the video in db or file does not have a valid uuid, it will add it in both the db and on the file.
                if (! Uuid::isValid($uuid ?? '')) {
                    if (! isset($this->fileMetaData['tags']['uid']) && ! isset($this->fileMetaData['tags']['uuid'])) {
                        $uuid = Str::uuid()->toString();
                        $this->embedChain[] = new EmbedUidInMetadata($filePath, $uuid, $this->taskId, $video->id);
                    } else {
                        $uuid = $this->fileMetaData['tags']['uuid'] ?? $this->fileMetaData['tags']['uid']; // Neet to use UUID everywhere instead of mismatching uuid with uid
                        dump("Found UUID {$uuid}");
                        $video->update(['uuid' => $uuid]); // If embedding, video is updated in the embed job
                    }
                }

                $metadata = Metadata::where('uuid', $uuid)->orWhere('composite_id', $compositeId)->first();

                if (! $metadata) {
                    $metadata = Metadata::create(['uuid' => $uuid, 'composite_id' => $compositeId, 'video_id' => $video->id]);
                }

                $stored = $metadata->toArray();
                $fileUpdated = ! is_null($metadata->date_scanned) && filemtime($filePath) > strtotime($metadata->date_scanned);

                if (is_null($metadata->uuid) || $fileUpdated) {
                    $changes['uuid'] = $uuid;
                }

                if (is_null($metadata->composite_id)) {
                    $changes['composite_id'] = $compositeId;
                }

                if (is_null($metadata->file_size) || $fileUpdated) {
                    $changes['file_size'] = filesize($filePath);
                }

                if (is_null($metadata->mime_type) || $metadata->mime_type === 'application/octet-stream' || $metadata->mime_type === 'application/x-genesis-rom') {
                    $changes['mime_type'] = $this->extractMimeType($filePath);
                }

                if (is_null($metadata->date_uploaded) || $fileUpdated) {
                    $mtime = filemtime($filePath);
                    $ctime = filectime($filePath);

                    $changes['date_uploaded'] = date('Y-m-d h:i A', $mtime < $ctime ? $mtime : $ctime);
                }

                $mime_type = $changes['mime_type'] ?? $metadata->mime_type;
                $is_audio = str_starts_with($mime_type, 'audio');
                $media_type = $is_audio ? MediaType::AUDIO : MediaType::VIDEO;

                if ($stored['media_type'] !== $media_type) {
                    $changes['media_type'] = $media_type;
                }
                // if file is of type audio and one of the following is true: artist is null, album is null, codec is null, bitrate is null => generate description from audio tags
                $audioMetadata = (is_null($metadata->artist) || is_null($metadata->album) || is_null($metadata->bitrate) || is_null($metadata->codec)) && $is_audio ? $this->getAudioDescription($filePath, $this->fileMetaData ?? null) : [];

                // TODO: if no poster_url is set or file was modified since last update and the file is of type audio, extract image
                // TODO: if poster_url is set and it is not a local url, download and save as local image
                if ($is_audio && (empty($metadata->poster_url) || ($metadata->updated_at ?? $metadata->created_at)?->getTimestamp() < filemtime($filePath))) {
                    $relativePath = "{$video->folder->path}/{$metadata->id}";
                    $coverArtPath = "posters/audio/{$relativePath}-{$uuid}.png";

                    if ($coverArtUrl = $this->checkAlbumArt($filePath, $coverArtPath, $fileUpdated)) {
                        $changes['poster_url'] = $coverArtUrl;
                    }
                }

                preg_match('![sS]\d+!', $video->name, $seasonRaw);
                preg_match('![eE]\d+!', $video->name, $episodeRaw);
                preg_match('!\d+!', $seasonRaw[0] ?? '', $season);
                preg_match('!\d+!', $episodeRaw[0] ?? '', $episode);

                if (is_null($metadata->duration) || $fileUpdated) {
                    $this->confirmMetadata($filePath);
                    $duration = $this->fileMetaData['format']['duration'] ?? $this->fileMetaData['streams'][0]['duration'] ?? null;
                    $changes['duration'] = is_numeric($duration) ? floor($duration) : null;
                }

                if (! $is_audio && (is_null($metadata->resolution_height) || is_null($metadata->codec) || $fileUpdated)) {
                    $this->confirmMetadata($filePath);
                    foreach ($this->fileMetaData['streams'] as $stream) {
                        if (! isset($stream['codec_type']) || $stream['codec_type'] !== 'video' || ! isset($stream['width'])) {
                            continue;
                        }

                        $changes['resolution_width'] = floor($stream['width']);

                        if (isset($stream['height'])) {
                            $changes['resolution_height'] = floor($stream['height']);
                        }
                        if (isset($stream['codec_name'])) {
                            $changes['codec'] = $stream['codec_name'];
                        }
                        if (isset($stream['avg_frame_rate'])) {
                            [$numerator, $denominator] = explode('/', $stream['avg_frame_rate']);
                            $changes['frame_rate'] = floor($numerator / max($denominator, 1));
                        }

                        break;
                    }
                }

                // Update episode and season (track and disc) if not set or file is audio and has embedded info and was updated
                if (is_null($metadata->episode) || ($fileUpdated && $is_audio && isset($audioMetadata['episode']))) {
                    $changes['episode'] = count($episode) == 1 ? (int) $episode[0] : $audioMetadata['episode'] ?? null;
                }
                if (is_null($metadata->season) || ($fileUpdated && $is_audio && isset($audioMetadata['season']))) {
                    $changes['season'] = count($season) == 1 ? (int) $season[0] : $audioMetadata['season'] ?? null;
                }

                if (is_null($metadata->title) && ! $is_audio) {
                    $newTitle = count($season) == 1 ? 'S' . $season[0] : '';
                    $newTitle .= count($episode) == 1 ? 'E' . $episode[0] : '';

                    if ($newTitle != '') {
                        $changes['title'] = $newTitle;
                    } else {
                        $changes['title'] = $video->name;
                    }
                }

                // Only update title from audioMetadata if not set or file is of type audio with an embedded title and was updated
                if ((is_null($metadata->title) || $fileUpdated) && $is_audio && isset($audioMetadata['title'])) {
                    $changes['title'] = $audioMetadata['title'];
                }

                if (is_null($metadata->date_released)) {
                    $changes['date_released'] = null;
                }

                // What?
                if (is_null($metadata->editor_id)) {
                    $changes['editor_id'] = null;
                }

                if (is_null($metadata->description)) {
                    $changes['description'] = $audioMetadata['description'] ?? $video->description ?? null;
                }

                if (is_null($metadata->lyrics) || $fileUpdated) {
                    $changes['lyrics'] = $audioMetadata['lyrics'] ?? null;
                }

                if (is_null($metadata->artist) || $fileUpdated) {
                    $changes['artist'] = $audioMetadata['artist'] ?? null;
                }

                if (is_null($metadata->album) || $fileUpdated) {
                    $changes['album'] = $audioMetadata['album'] ?? null;
                }

                if (is_null($metadata->codec) && ! isset($changes['codec'])) {
                    $changes['codec'] = $audioMetadata['codec'] ?? null;
                }

                if ((is_null($metadata->bitrate) || $fileUpdated) && ! isset($changes['bitrate'])) {
                    $changes['bitrate'] = $audioMetadata['bitrate'] ?? null;
                }

                if (is_null($metadata->view_count)) {
                    $changes['view_count'] = ($metadata->id ? Record::where('metadata_id', $metadata->id)->count() : 0);
                }

                if (! empty($changes)) {
                    $changes['date_scanned'] = date('Y-m-d h:i:s A');
                    array_push($transactions, [...$stored, ...$changes]);
                    /**
                     * DEBUG
                     * dump(count([...$stored, ...$changes]));
                     * if ($new) dump([...$stored, ...$changes]);
                     * dump([...$stored, ...$changes]);
                     * dump($changes);
                     * dump($video->name);
                     */
                }
                $index += 1;
                $taskService->updateSubTask($this->subTaskId, ['progress' => (int) (($index / count($this->videos)) * 100)]);
            }
        } catch (\Throwable $th) {
            $ids = array_column($transactions, 'id');

            $error = true;
            $this->handleError('Cannot verify file metadata', $th, $ids, count($transactions), count($this->videos));
        }

        try {
            if (empty($transactions) || $error) {
                return 'No Changes Found';
            }

            Metadata::upsert(
                $transactions,
                'id',
                [
                    'video_id',
                    'title',
                    'description',
                    'lyrics',
                    'artist',
                    'album',
                    'duration',
                    'season',
                    'episode',
                    'view_count',
                    'uuid',
                    'file_size',
                    'mime_type',
                    'codec',
                    'bitrate',
                    'resolution_width',
                    'resolution_height',
                    'frame_rate',
                    'poster_url',
                    'date_scanned',
                    'date_uploaded',
                    'media_type',
                ]
            );

            $summary = 'Updated ' . count($transactions) . ' videos from id ' . ($transactions[0]['video_id']) . ' to ' . ($transactions[count($transactions) - 1]['video_id']);
            dump($summary);

            return $summary;
        } catch (\Throwable $th) {
            $ids = array_column($transactions, 'id');
            $this->handleError('Error inserting verified file metadata', $th, $ids, count($transactions));
        }
    }

    public static function getFileMetadata($filePath) {
        try {
            // ? FFMPEG module with 6 test folders takes 35+ seconds but running the commands through shell takes 18 seconds

            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            dump('PULLING METADATA ' . $filePath);
            $command = [
                'ffprobe',
                '-v',
                'quiet',
                '-print_format',
                'json',
                '-show_format',
                '-show_streams',
                $filePath,
            ];

            $process = new Process($command);
            $process->run();

            // Check if the process was successful
            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = $process->getOutput();
            $metadata = json_decode($output, true);
            if ($ext === 'ogg') {
                $metadata['format'] = $metadata['streams'][0] ?? [];
            }

            if (! isset($metadata['format']['tags']['uuid']) && isset($metadata['format']['tags']['uid'])) {
                $metadata['format']['tags']['uuid'] = $metadata['format']['tags']['uid'];
            } // old uid tag
            if (! isset($metadata['format']['tags']['uuid']) && isset($metadata['format']['tags']['encoder']) && uuid_is_valid($metadata['format']['tags']['encoder'])) {
                $metadata['format']['tags']['uuid'] = $metadata['format']['tags']['encoder'];
            } // ExifTool tag

            return [
                'format' => $metadata['format'] ?? [],
                'tags' => $metadata['format']['tags'] ?? [],
                'streams' => $metadata['streams'] ?? [],
            ];
        } catch (\Throwable $th) {
            dump($th);
            Log::error('Unable to get file metadata', ['error' => $th->getMessage()]);

            return ['format' => [], 'tags' => [], 'streams' => []];
        }
    }

    // Call get metadata when needed
    private function confirmMetadata($filePath) {
        if (is_null($this->fileMetaData) || count($this->fileMetaData) == 0) {
            $this->fileMetaData = $this->getFileMetadata($filePath);
        }
    }

    private function getAudioDescription($filePath) {
        $this->confirmMetadata($filePath);

        $tags = $this->fileMetaData['tags'] ?? [];
        $streams = $this->fileMetaData['streams'] ?? [];

        $artist = $tags['artist'] ?? $tags['ARTIST'] ?? '';
        $album = $tags['album'] ?? $tags['ALBUM'] ?? '';

        $description = implode(' - ', array_filter([
            $album,
            $artist,
        ]));

        $results = [
            'title' => $tags['title'] ?? $tags['TITLE'] ?? null,
            'description' => $description ?: '',
            'season' => isset($tags['disc']) ? (int) explode('/', $tags['disc'])[0] : 0,
            'episode' => isset($tags['track']) ? (int) explode('/', $tags['track'])[0] : 0,
            'lyrics' => $tags['lyrics-   '] ?? $tags['UNSYNCEDLYRICS'] ?? null,
            'album' => $album ?? null,
            'artist' => $artist ?? null,
        ];

        foreach ($streams as $stream) {
            if (! $stream['codec_type'] == 'audio') {
                continue;
            }

            $results = array_merge($results, array_filter([
                'codec' => $stream['codec_name'] ?? null,
                'bitrate' => $stream['bit_rate'] ?? null,
            ]));
            break;
        }

        if (isset($this->fileMetaData['format']['bit_rate']) && (! isset($results['bitrate']) || is_null($results['bitrate']))) {
            $results['bitrate'] = $this->fileMetaData['format']['bit_rate'];
        }

        return $results;
    }

    public static function getPathUrl($path) {
        /**
         * @disregard P1013 Undefined method but it actually exists
         */
        return Storage::disk('public')->url($path);
    }

    private function checkAlbumArt($filePath, $coverArtPath, $recentlyUpdated = false) {
        // If album art already exists and the file has not been updated since last scan date (cover art has not changed) then just return the existing image
        if (Storage::disk('public')->exists($coverArtPath) && ! $recentlyUpdated) {
            return $this->getPathUrl($coverArtPath);
        }

        // If album art exists and the file was recently updated, overwrite the old image

        $coverGenerated = $this->extractAlbumArt($filePath, $coverArtPath);

        if (! $coverGenerated) {
            return null;
        }

        Storage::disk('public')->put($coverArtPath, $coverGenerated);

        return $this->getPathUrl($coverArtPath);
    }

    private function extractAlbumArt($filePath, $outputPath): string|false {
        $result = false;
        $tempPath = sys_get_temp_dir() . '/' . basename($outputPath);

        try {
            $command = [
                'ffmpeg',
                '-i',
                $filePath,
                '-an',
                '-vcodec',
                'copy',
                $tempPath,
            ];

            $process = new Process($command);
            $process->run();

            if (! $process->isSuccessful()) {
                $errorOutput = $process->getErrorOutput(); // Checks if error is caused by missing album art (never set so dont log)
                if (str_contains($errorOutput, 'Output file does not contain any stream')) {
                    return false;
                }

                throw new ProcessFailedException($process);
            }

            if (file_exists($tempPath) && filesize($tempPath) > 0) {
                $result = file_get_contents($tempPath);
            }
        } catch (\Throwable $th) {
            dump($th->getMessage());
            Log::error($th);
        } finally {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }

        return $result;
    }

    protected function extractMimeType($filePath) {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filePath);

        if ($mimeType === 'application/octet-stream' || $mimeType === 'application/x-genesis-rom') {
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);

            $mimeMap = [
                'mp3' => 'audio/mpeg',
                'wav' => 'audio/wav',
                'ogg' => 'audio/ogg',
                'aac' => 'audio/aac',
                'flac' => 'audio/flac',
            ];

            return $mimeMap[strtolower($extension)] ?? $mimeType;
        }

        return $mimeType;
    }
}
