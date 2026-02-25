<?php

namespace App\Jobs;

use App\Enums\MediaType;
use App\Enums\TaskStatus;
use App\Models\Metadata;
use App\Models\Record;
use App\Models\SubTask;
use App\Models\Subtitle;
use App\Services\Subtitles\SubtitleScanner;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class VerifyFiles extends ManagedSubTask {
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

    public function handle(TaskService $taskService, SubtitleScanner $subtitleScanner): void {
        if (! $this->beginSubTask($taskService)) {
            return;
        }

        try {
            $summary = $this->verifyFiles($taskService, $subtitleScanner);
            $taskCountUpdates = count($this->embedChain) ? ['sub_tasks_complete' => '++', 'sub_tasks_total' => count($this->embedChain), 'sub_tasks_pending' => count($this->embedChain)] : ['sub_tasks_complete' => '++'];

            $this->completeSubTask($taskService, $summary, $taskCountUpdates);

            // Starts other subtasks after updating current subtask and parent subtask states
            // The parent task "ends" after the batch empties so in theory, this should delay that anyways and does not need to run before the task update
            foreach ($this->embedChain as $embedTask) {
                Bus::dispatch($embedTask);
            }
        } catch (\Throwable $th) {
            $this->failSubTask($taskService, $th);
            throw $th;
        }
    }

    private function verifyFiles(TaskService $taskService, SubtitleScanner $subtitleScanner) {
        $metadataTransactions = [];
        $subtitleTransactions = [];
        $scannedDirectories = []; // TODO: this should really go in the indexer or a broken down part of the indexer

        $error = false;
        $index = 0;

        if (count($this->videos) == 0) {
            throw new \Exception('Video Data Lost');
        }

        try {
            foreach ($this->videos as $video) {
                $baseName = basename($video->path);
                $fileName = pathinfo($video->path, PATHINFO_FILENAME);
                $compositeId = $video->folder->path . '/' . $baseName;

                // absolute file path in storage
                $filePath = $this->getAbsoluteMediaPath($video);
                $folderPath = dirname($filePath);

                /**
                 * @disregard P1013 Undefined method but it actually exists
                 */
                if (! Storage::disk('public')->fileExists("media/{$video->folder->path}/{$baseName}")) {
                    throw new \Exception("File media/{$video->folder->path}/{$baseName} no longer exists. Index your videos before running this task again.");
                }

                // enforce loading file metadata if uuid is missing
                $this->fileMetaData = is_null($video->uuid) ? $this->getFileMetadata($filePath, 'uuid missing') : [];
                $uuid = $video->uuid ?? '';

                // handle missing or invalid Uuid
                if (! Uuid::isValid($uuid)) {
                    $uuid = $this->resolveMediaUuid($video, $filePath);
                }

                /**
                 * metadata should be defined by uuid alone but when I tried to do this, there were duplicates and issues so this needs investigation and fixing
                 * $metadata = Metadata::firstOrCreate(['uuid' => $uuid],['video_id' => $video->id, 'composite_id' => $compositeId]);
                 */
                $metadata = Metadata::where('uuid', $uuid)->orWhere('composite_id', $compositeId)->first();

                if (! $metadata) {
                    $metadata = Metadata::create(['uuid' => $uuid, 'composite_id' => $compositeId, 'video_id' => $video->id]);
                }

                $stored = $metadata->toArray(); // Metadata from db
                $changes = []; // Changes -> stored + changes . length has to be the same for every video so must generate defaults

                $fileUpdated = $metadata->file_scanned_at && filemtime($filePath) > $metadata->file_scanned_at->timestamp;

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

                $file_modified_at = $metadata->file_modified_at;

                if (is_null($metadata->file_modified_at) || $fileUpdated) {
                    $mtime = filemtime($filePath);
                    $ctime = filectime($filePath);

                    $file_modified_at = Carbon::createFromTimestampUTC(min($mtime, $ctime));
                    $changes['file_modified_at'] = $file_modified_at;
                }

                if (is_null($metadata->first_file_modified_at) && $file_modified_at !== null) {
                    $changes['first_file_modified_at'] = $file_modified_at;
                }

                $mime_type = $changes['mime_type'] ?? $metadata->mime_type;
                $is_audio = str_starts_with($mime_type, 'audio');
                $media_type = $is_audio ? MediaType::AUDIO : MediaType::VIDEO;

                if ($stored['media_type'] !== $media_type) {
                    $changes['media_type'] = $media_type;
                }
                // if file is of type audio and one of the following is true: artist is null, album is null, codec is null, bitrate is null => generate description from audio tags
                $audioMetadata = ($fileUpdated || is_null($metadata->artist) || is_null($metadata->album) || is_null($metadata->bitrate) || is_null($metadata->codec)) && $is_audio ? $this->getAudioDescription($filePath, $this->fileMetaData ?? null) : [];

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
                    $this->confirmMetadata($filePath, 'Duration is missing or file was updated');
                    $duration = $this->fileMetaData['format']['duration'] ?? $this->fileMetaData['streams'][0]['duration'] ?? $metadata->duration;
                    $changes['duration'] = is_numeric($duration) ? floor($duration) : null;
                }

                // Embedded Subtitles

                $subtitleScanNeeded = ! $is_audio && is_null($metadata->subtitles_scanned_at) || $fileUpdated;

                if ($subtitleScanNeeded) {
                    $this->confirmMetadata($filePath, 'Subtitle scan date is missing or file was updated');
                    $embeddedSubtitleTransactions = $subtitleScanner->scanEmbeddedSubtitles($uuid, $this->fileMetaData);

                    foreach ($embeddedSubtitleTransactions as $tx) {
                        $subtitleTransactions[] = $tx;
                    }
                }

                // #region External Subtitles

                // check if directory subtitle scan is needed

                if (! isset($scannedDirectories[$folderPath])) {
                    $scannedDirectories[$folderPath] = [
                        'last_modified' => filemtime($folderPath),
                        'external_subtitles' => null,
                    ];
                }

                $dirUpdated = $metadata->file_scanned_at && $scannedDirectories[$folderPath]['last_modified'] > $metadata->file_scanned_at->timestamp;

                // if directory updated, check directory for related subtitle files and make subtitle transactions for them with stream 0

                // Scan each directory once per batch
                if (! $is_audio && ($subtitleScanNeeded || $dirUpdated) && is_null($scannedDirectories[$folderPath]['external_subtitles'])) {
                    $scannedDirectories[$folderPath]['external_subtitles'] = $subtitleScanner->findExternalSubtitlesInDirectory($folderPath);
                }

                // if subtitles in this directory have been scanned, check for matches
                if (! is_null($scannedDirectories[$folderPath]['external_subtitles'])) {
                    $relevantSubtitles = array_filter(
                        $scannedDirectories[$folderPath]['external_subtitles'],
                        fn ($sub) => strtolower($sub['media_filename']) === strtolower($fileName)
                    );

                    $externalSubtitleTransactions = $subtitleScanner->buildSubtitleTransactions(
                        $uuid,
                        $relevantSubtitles
                    );

                    foreach ($externalSubtitleTransactions as $tx) {
                        $subtitleTransactions[] = $tx;
                    }
                }

                if ($subtitleScanNeeded || $dirUpdated) {
                    $changes['subtitles_scanned_at'] = now();
                }

                // #endregion

                if (! $is_audio && (is_null($metadata->resolution_height) || is_null($metadata->codec) || $fileUpdated)) {
                    $this->confirmMetadata($filePath, "Not audio and missing resolution or codec or because fileUpdated was {$fileUpdated}");
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
                    $changes['title'] = $audioMetadata['title'] ?? $metadata->title;
                }

                if (is_null($metadata->description)) {
                    $changes['description'] = $audioMetadata['description'] ?? $video->description ?? null;
                }

                if (is_null($metadata->lyrics) || $fileUpdated) {
                    $changes['lyrics'] = $audioMetadata['lyrics'] ?? $metadata->lyrics; // Default to existing
                }

                if (is_null($metadata->artist) || $fileUpdated) {
                    $changes['artist'] = $audioMetadata['artist'] ?? $metadata->artist;
                }

                if (is_null($metadata->album) || $fileUpdated) {
                    $changes['album'] = $audioMetadata['album'] ?? $metadata->album;
                }

                if (is_null($metadata->codec) && ! isset($changes['codec'])) {
                    $changes['codec'] = $audioMetadata['codec'] ?? $metadata->codec;
                }

                if ((is_null($metadata->bitrate) || $fileUpdated) && ! isset($changes['bitrate']) && ! $is_audio) {
                    $this->confirmMetadata($filePath, 'Bitrate is missing on video');
                    $changes['bitrate'] = $this->fileMetaData['format']['bit_rate'] ?? $metadata->bitrate;
                }

                if (is_null($metadata->view_count)) {
                    $changes['view_count'] = ($metadata->id ? Record::where('metadata_id', $metadata->id)->count() : 0);
                }

                if (! empty($changes)) {
                    $changes['file_scanned_at'] = now();

                    unset(
                        $stored['created_at'],
                        $stored['updated_at'],
                        $stored['logical_composite_id']
                    );

                    $metadataTransactions[] = [...$stored, ...$changes];
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
            $ids = array_column($metadataTransactions, 'id');

            $error = true;
            $this->handleError('Cannot verify file metadata', $th, $ids, count($metadataTransactions), count($this->videos));
        }

        try {
            if (empty($metadataTransactions) || $error) {
                return 'No Changes Found';
            }

            Metadata::upsert(
                $metadataTransactions,
                'uuid',
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
                    'file_scanned_at',
                    'file_modified_at',
                    'first_file_modified_at',
                    'media_type',
                    'subtitles_scanned_at',
                ]
            );

            $summary = 'Updated ' . count($metadataTransactions) . ' videos from id ' . ($metadataTransactions[0]['video_id']) . ' to ' . ($metadataTransactions[count($metadataTransactions) - 1]['video_id']);

            if (! empty($subtitleTransactions)) {
                Subtitle::upsert($subtitleTransactions, ['metadata_uuid', 'source_key'], ['language', 'codec', 'is_default', 'is_forced', 'external_path']);
                $summary .= ' and found ' . count($subtitleTransactions) . ' subtitle track(s)';
            }

            return $summary;
        } catch (\Throwable $th) {
            $ids = array_column($metadataTransactions, 'id');
            $this->handleError('Error inserting verified file metadata', $th, $ids, count($metadataTransactions));
        }
    }

    public static function getFileMetadata($filePath, $reason = 'und') {
        try {
            // ? FFMPEG module with 6 test folders takes 35+ seconds but running the commands through shell takes 18 seconds

            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            if (config('app.env') === 'local') {
                dump('PULLING METADATA ' . $filePath . ' reason: ' . $reason);
            }
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

            if (! is_array($metadata)) {
                throw new \RuntimeException('Invalid ffprobe JSON output');
            }

            if ($ext === 'ogg') {
                $metadata['format'] = $metadata['streams'][0] ?? [];
            }

            $format = $metadata['format'] ?? [];
            $tags = array_change_key_case($format['tags'] ?? [], CASE_LOWER);
            $streams = $metadata['streams'] ?? [];

            if (! isset($tags['uuid']) && isset($tags['uid'])) {
                $tags['uuid'] = $tags['uid']; // Old uid tag, does not apply to any versions from 2025 and up
            }

            if (! isset($tags['uuid']) && isset($tags['encoder']) && uuid_is_valid($tags['encoder'])) {
                $tags['uuid'] = $tags['encoder']; // ExifTool tag
            }

            return [
                'format' => $format,
                'tags' => $tags,
                'streams' => $streams,
            ];
        } catch (\Throwable $th) {
            Log::error('Unable to get file metadata', ['path' => $filePath, 'error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);

            return ['format' => [], 'tags' => [], 'streams' => []];
        }
    }

    // Call get metadata when needed
    private function confirmMetadata(string $filePath, string $reason = 'und') {
        if (is_null($this->fileMetaData) || count($this->fileMetaData) == 0) {
            $this->fileMetaData = $this->getFileMetadata($filePath, $reason);
        }
    }

    private function getAudioDescription($filePath) {
        $this->confirmMetadata($filePath, 'Get audio description');

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

    protected function getAbsoluteMediaPath($media) {
        return str_replace('\\', '/', Storage::disk('public')->path('')) . "media/{$media->folder->path}/" . basename($media->path);
    }

    protected function resolveMediaUuid($media, $filePath) {
        // if the media in db or file does not have a valid uuid, it will add it in both the db and on the file.
        if (! isset($this->fileMetaData['tags']['uuid'])) {
            $uuid = Str::uuid()->toString();
            $this->embedChain[] = new EmbedUidInMetadata($filePath, $uuid, $this->taskId, $media->id);
        } else {
            $uuid = $this->fileMetaData['tags']['uuid'];
            dump("Found UUID on file {$uuid}");
            $media->update(['uuid' => $uuid]); // If embedding, media file is updated in the embed job
        }

        return $uuid;
    }
}
