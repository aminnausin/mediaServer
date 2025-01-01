<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\Metadata;
use App\Models\Record;
use App\Models\SubTask;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class VerifyFiles implements ShouldQueue {
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $taskId;

    protected $subTaskId;

    protected $startedAt;

    /**
     * Create a new job instance.
     */
    public function __construct(public $videos, $taskId) {
        //
        $subTask = SubTask::create(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => 'Verify ' . count($videos) . ' Files']); //
        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

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
     */
    public function handle(): void {
        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...
            SubTask::where('id', $this->subTaskId)->update(['status' => TaskStatus::CANCELLED, 'summary' => 'Parent Task was Cancelled']);

            return;
        }

        $this->startedAt = now();
        DB::table('tasks')->where('id', $this->taskId)->decrement('sub_tasks_pending');
        SubTask::where('id', $this->subTaskId)->update(['status' => TaskStatus::PROCESSING, 'started_at' => $this->startedAt]);

        try {
            $summary = $this->verifyFiles();
            $endedAt = now();
            $duration = (int) $this->startedAt->diffInSeconds($endedAt);
            DB::table('tasks')->where('id', $this->taskId)->increment('sub_tasks_complete');
            SubTask::where('id', $this->subTaskId)->update([
                'status' => TaskStatus::COMPLETED,
                'summary' => $summary,
                'progress' => 100,
                'ended_at' => $endedAt,
                'duration' => $duration,
            ]);
        } catch (\Throwable $th) {
            $endedAt = now();
            $duration = (int) $this->startedAt->diffInSeconds($endedAt);
            DB::table('tasks')->where('id', $this->taskId)->increment('sub_tasks_failed');
            SubTask::where('id', $this->subTaskId)->update(['status' => TaskStatus::FAILED, 'summary' => 'Error: ' . $th->getMessage(), 'ended_at' => $endedAt, 'duration' => $duration]);
            throw $th;
        }
    }

    private function verifyFiles() {
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
                // $filePath = str_replace('\\', '/', Storage::path('app/private/')) . 'media/' . $video->folder->path . "/" . basename($video->path);
                $fileMetaData = is_null($video->uuid) ? $this->getFileMetadata($filePath) : []; // Empty unless uuid is missing or duration is missing
                $uuid = $video->uuid ?? ''; // video has ? file has

                // if the video in db or file does not have a valid uuid, it will add it in both the db and on the file.
                if (! Uuid::isValid($uuid ?? '')) {
                    if (! isset($fileMetaData['tags']['uid'])) {
                        $uuid = Str::uuid()->toString();
                        EmbedUidInMetadata::dispatch($filePath, $uuid, $this->taskId);
                    } else {
                        $uuid = $fileMetaData['tags']['uid'];
                        dump("Found UUID {$uuid}");
                    }
                    $video->update(['uuid' => $uuid]);
                    // if (isset($fileMetaData['tags']['uid']) ? $fileMetaData['tags']['uid'] : false) dump('Embed'); //EmbedUidInMetadata::dispatch($filePath, $uuid);
                }

                $metadata = Metadata::where('uuid', $uuid)->orWhere('composite_id', $compositeId)->first();

                if (! $metadata) {
                    $metadata = Metadata::create(['uuid' => $uuid, 'composite_id' => $compositeId, 'video_id' => $video->id]);
                }

                $stored = $metadata->toArray();
                if (is_null($metadata->uuid)) {
                    $changes['uuid'] = $uuid;
                }

                if (is_null($metadata->composite_id)) {
                    $changes['composite_id'] = $compositeId;
                }

                if (is_null($metadata->file_size)) {
                    $changes['file_size'] = filesize($filePath);
                }

                if (is_null($metadata->mime_type)) {
                    $changes['mime_type'] = File::mimeType($filePath) ?? null;
                }

                $mime_type = isset($changes['mime_type']) ? $changes['mime_type'] : $metadata->mime_type;

                $audioMetadata = (is_null($metadata->description) || (is_null($metadata->episode) && is_null($metadata->season))) && str_starts_with($mime_type, 'audio') ? $this->getAudioDescription($filePath, $fileMetaData ?? null) : [];

                if (is_null($metadata->poster_url) && $mime_type && str_starts_with($mime_type, 'audio')) {
                    $relativePath = $video->folder->path . '/' . $metadata->id;
                    $coverArtPath = "posters/audio/$relativePath-$uuid.png";

                    $coverArtUrl = $this->checkAlbumArt($filePath, $coverArtPath);
                    if ($coverArtUrl) {
                        $changes['poster_url'] = $coverArtUrl;
                    }
                }

                preg_match('![sS][0-9]+!', $video->name, $seasonRaw);
                preg_match('![eE][0-9]+!', $video->name, $episodeRaw);
                preg_match('![0-9]+!', $seasonRaw[0] ?? '', $season);
                preg_match('![0-9]+!', $episodeRaw[0] ?? '', $episode);

                if (is_null($metadata->duration)) {
                    if (! isset($fileMetaData['duration'])) {
                        $fileMetaData = $this->getFileMetadata($filePath);
                    }

                    $duration = isset($fileMetaData['duration']) ? floor($fileMetaData['duration']) : null;
                    $changes['duration'] = $duration;
                }

                if (is_null($metadata->episode)) {
                    $changes['episode'] = count($episode) == 1 ? (int) $episode[0] : $audioMetadata['episode'] ?? null;
                }
                if (is_null($metadata->season)) {
                    $changes['season'] = count($season) == 1 ? (int) $season[0] : $audioMetadata['season'] ?? null;
                }

                if (is_null($metadata->title) && ! str_starts_with($mime_type, 'audio')) {
                    $newTitle = count($season) == 1 ? 'S' . $season[0] : '';
                    $newTitle .= count($episode) == 1 ? 'E' . $episode[0] : '';

                    if ($newTitle != '') {
                        $changes['title'] = $newTitle;
                    } else {
                        $changes['title'] = $video->name;
                    }
                }

                if (is_null($metadata->date_released)) {
                    $changes['date_released'] = null;
                }

                if (is_null($metadata->editor_id)) {
                    $changes['editor_id'] = null;
                }

                if (is_null($metadata->description)) {
                    $changes['description'] = $audioMetadata['description'] ?? $video->description ?? null;
                }

                is_null($metadata->view_count) ? $changes['view_count'] = Record::where('video_id', $video->id)->count() + ($metadata->id ? Record::where('metadata_id', $metadata->id)->count() : 0) : $stored['view_count'] = $metadata->view_count;

                if (count($changes) > 0) {
                    $changes['date_scanned'] = date('Y-m-d h:i:s A');
                    array_push($transactions, [...$stored, ...$changes]);
                    // dump(count([...$stored, ...$changes]));
                    // if ($new) dump([...$stored, ...$changes]);
                    // dump([...$stored, ...$changes]);
                    // dump($changes);
                    // dump($video->name);
                }
                $index += 1;
                SubTask::where('id', $this->subTaskId)->update(['progress' => (int) (($index / count($this->videos)) * 100)]);

                // dump($metadata->toArray());
            }
        } catch (\Throwable $th) {
            $ids = array_map(function ($transaction) {
                return $transaction['id'];
            }, $transactions);

            $error = true;
            $errorMessage = 'cannot verify file metadata ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates and ' . count($this->videos) . ' checks with IDs ' . json_encode($ids);
            dump($errorMessage);

            throw new \Exception($errorMessage);
        }

        try {
            if (count($transactions) == 0 || $error == true) {
                return 'No Changes Found';
            }

            Metadata::upsert($transactions, 'id', ['video_id', 'title', 'description', 'duration', 'season', 'episode', 'view_count', 'uuid', 'file_size', 'mime_type', 'poster_url', 'date_scanned']);

            $summary = 'Updated ' . count($transactions) . ' videos from id ' . ($transactions[0]['video_id']) . ' to ' . ($transactions[count($transactions) - 1]['video_id']);
            dump($summary);

            return $summary;
        } catch (\Throwable $th) {
            $ids = array_map(function ($transaction) {
                return $transaction['id'];
            }, $transactions);

            $errorMessage = 'Error cannot insert verified file metadata ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates with IDs ' . json_encode($ids); // . [...$ids]);
            dump($errorMessage);

            throw new \Exception($errorMessage);
        }
    }

    private function getFileMetadata($filePath) {
        try {
            // ? FFMPEG module with 6 test folders takes 35+ seconds but running the commands through shell takes 18 seconds
            // $ffprobe = FFMpegFFProbe::create();
            // $tags = $ffprobe->format($filePath)->get('tags'); // extracts file information
            // return isset($tags['uid']) ? $tags['uid'] : (isset($tags['UID']) ? $tags['UID'] : null);

            $command = [
                'ffprobe',
                '-v',
                'quiet',
                '-print_format',
                'json',
                '-show_format',
                $filePath,
            ];

            $process = new Process($command);
            $process->run();

            // Check if the process was successful
            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = $process->getOutput(); // Decode JSON output
            $metadata = json_decode($output, true);

            return $metadata['format'];
        } catch (\Throwable $th) {
            dump($th);

            return ['tags' => []];
        }
    }

    private function getAudioDescription($filePath, $metadata) {
        if (is_null($metadata) || count($metadata) == 0) {
            $metadata = $this->getFileMetadata($filePath);
        }

        $description = $metadata['tags']['artist'] ?? '';
        $description = ($description ? ($description . ' - ') : '') . ($metadata['tags']['album'] ?? '');
        $season = $metadata['tags']['disc'] ?? null;
        $episode = $metadata['tags']['track'] ?? null;

        return ['description' => $description === '' ? null : $description, 'season' => $season, 'episode' => $episode];
    }

    private function getPathUrl($path) {
        /**
         * @disregard P1013 Undefined method but it actually exists
         */
        return Storage::disk('public')->url($path);
    }

    private function checkAlbumArt($filePath, $coverArtPath) {
        if (Storage::disk('public')->exists($coverArtPath)) {
            return $this->getPathUrl($coverArtPath);
        } else {
            $coverGenerated = $this->extractAlbumArt($filePath, $coverArtPath);
            if ($coverGenerated) {
                Storage::disk('public')->put($coverArtPath, $coverGenerated);

                return $this->getPathUrl($coverArtPath);
            }
        }

        return null;
    }

    private function extractAlbumArt($filePath, $outputPath) {
        try {
            //code...'ffmpeg',
            $tempPath = sys_get_temp_dir() . '/' . basename($outputPath);
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
                if (strpos($errorOutput, 'Output file does not contain any stream') !== false) {
                    return false;
                }

                throw new ProcessFailedException($process);
            }

            if (! file_exists($tempPath) || filesize($tempPath) == 0) {
                unlink($tempPath);

                return false;
            }

            $coverArtContent = file_get_contents($tempPath);
            unlink($tempPath);

            return $coverArtContent;
        } catch (\Throwable $th) {
            dump($th->getMessage());
            Log::error($th);

            return false;
        }
    }
}
