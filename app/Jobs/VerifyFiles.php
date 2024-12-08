<?php

namespace App\Jobs;

use App\Models\Metadata;
use App\Models\Record;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Str;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use FFMpeg\FFProbe as FFMpegFFProbe;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Ramsey\Uuid\Uuid;

class VerifyFiles implements ShouldQueue {
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public $videos) {
        //
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
            return;
        }

        if (count($this->videos) == 0) {
            dump('Video Data Lost');
            return;
        }

        $transactions = array();
        $error = false;
        try {
            foreach ($this->videos as $video) {

                $stored = array(); // Metadata from db
                $changes = array(); // Changes -> stored + changes . length has to be the same for every video so must generate defaults

                $compositeId = $video->folder->path . "/" . basename($video->path);
                $filePath = str_replace('\\', '/', Storage::path('')) . 'public/media/' . $video->folder->path . "/" . basename($video->path);
                // $filePath = str_replace('\\', '/', Storage::path('app/private/')) . 'media/' . $video->folder->path . "/" . basename($video->path);
                $fileMetaData = is_null($video->uuid) ? $this->getFileMetadata($filePath) : []; // Empty unless uuid is missing or duration is missing
                $uuid = $video->uuid ?? ''; // video has ? file has

                // if the video in db or file does not have a valid uuid, it will add it in both the db and on the file.
                if (!Uuid::isValid($uuid ?? '')) {
                    if (!isset($fileMetaData['tags']['uid'])) {
                        $uuid = Str::uuid()->toString();
                        EmbedUidInMetadata::dispatch($filePath, $uuid);
                    } else {
                        $uuid = $fileMetaData['tags']['uid'];
                        dump("Found UUID {$uuid}");
                    }
                    $video->update(['uuid' => $uuid]);
                    // if (isset($fileMetaData['tags']['uid']) ? $fileMetaData['tags']['uid'] : false) dump('Embed'); //EmbedUidInMetadata::dispatch($filePath, $uuid);
                }

                $metadata = Metadata::where('uuid', $uuid)->orWhere('composite_id', $compositeId)->first();

                if (!$metadata) {
                    $metadata = Metadata::create(['uuid' => $uuid, 'composite_id' => $compositeId, 'video_id' => $video->id]);
                    // dump('new');
                    // $new = true;
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

                preg_match('![sS][0-9]+!', $video->name, $seasonRaw);
                preg_match('![eE][0-9]+!', $video->name, $episodeRaw);
                preg_match('![0-9]+!', $seasonRaw[0] ?? '', $season);
                preg_match('![0-9]+!', $episodeRaw[0] ?? '', $episode);


                if (is_null($metadata->duration)) {
                    // dump(str_replace('\\', '/', Storage::path('')) . 'public/' . substr($video->path, 8));
                    if (!isset($fileMetaData['duration'])) $fileMetaData = $this->getFileMetadata($filePath);

                    // $ffprobe = FFMpegFFProbe::create();
                    // $duration = floor($ffprobe
                    // ->format($filePath) // extracts file information
                    // ->get('duration'));

                    $duration = isset($fileMetaData['duration']) ? floor($fileMetaData['duration']) : null;
                    $changes['duration'] = $duration;
                }

                if (is_null($metadata->episode)) $changes['episode'] = count($episode) == 1 ? (int)$episode[0] : null;
                if (is_null($metadata->season)) $changes['season'] = count($season) == 1 ? (int)$season[0] : null;

                if (is_null($metadata->title)) {
                    $newTitle = count($season) == 1 ? 'S' . $season[0] : '';
                    $newTitle .= count($episode) == 1 ? 'E' . $episode[0] : '';

                    if ($newTitle != '') $changes['title'] = $newTitle;
                    else $changes['title'] = $video->name;
                }

                if (is_null($metadata->description)) {
                    $changes['description'] = $video->description ?? null;
                }

                if (is_null($metadata->date_released)) {
                    $changes['date_released'] = null;
                }

                if (is_null($metadata->editor_id)) {
                    $changes['editor_id'] = null;
                }

                is_null($metadata->view_count) ? $changes['view_count'] = Record::where('video_id', $video->id)->count() + ($metadata->id ? Record::where('metadata_id', $metadata->id)->count() : 0) : $stored['view_count'] = $metadata->view_count;

                if (count($changes) > 0) {
                    $changes['date_scanned'] = date("Y-m-d h:i:s A");
                    array_push($transactions, [...$stored, ...$changes]);
                    // dump(count([...$stored, ...$changes]));
                    // if ($new) dump([...$stored, ...$changes]);
                    // dump([...$stored, ...$changes]);
                    // dump($changes);
                    // dump($video->name);
                }
                // dump($metadata->toArray());
            }
        } catch (\Throwable $th) {
            $ids = array_map(function ($transaction) {
                return $transaction['id'];
            }, $transactions);

            dump('Error cannot verify file metadata ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates with IDs ');
            dump([...$ids]);
            $error = true;
            throw $th;
        }

        try {
            if (count($transactions) == 0 || $error == true) return;
            Metadata::upsert($transactions, 'id', ['video_id', 'title', 'description', 'duration', 'season', 'episode', 'view_count', 'uuid', 'file_size', 'date_scanned']);
            // Video::upsert($transactions, 'id', ['title','duration','season','episode','view_count']);
            dump('Updated ' . count($transactions) . ' videos from id ' . ($transactions[0]['video_id']) . ' to ' . ($transactions[count($transactions) - 1]['video_id']));
        } catch (\Throwable $th) {

            $ids = array_map(function ($transaction) {
                return $transaction['id'];
            }, $transactions);

            dump('Error cannot insert verified file metadata ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates with IDs '); // . [...$ids]);
            dump([...$ids]);
            throw $th;
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
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = $process->getOutput(); // Decode JSON output
            $metadata = json_decode($output, true);
            return $metadata["format"];
        } catch (\Throwable $th) {
            dump($th);
            return array("tags" => array());
        }
    }
}
