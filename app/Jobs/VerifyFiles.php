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
        foreach ($this->videos as $video) {
            try {
                $stored = array(); // Metadata from db
                $changes = array(); // Changes -> stored + changes . length has to be the same for every video so must generate defaults

                // id NOT_NULL      -> INT8
                // video_id         -> INT8
                // composite_id     -> VARCHAR
                // title            -> VARCHAR
                // season           -> INT4
                // episode          -> INT4
                // duration         -> INT4
                // view_count       -> INT4
                // description      -> VARCHAR
                // date_released    -> DATE
                // editor_id        -> INT8
                // created_at       -> DATE
                // updated_at       -> DATE
                // uuid             -> uuid
                // file_size        -> INT8
                // date_scanned     -> INT8

                $filePath = str_replace('\\', '/', Storage::path('')) . 'public/media/' . $video->folder->path . "/" . basename($video->path);
                $compositeId = $video->folder->path . "/" . basename($video->path);
                $uuid = $video->uuid ?? Str::uuid()->toString();

                // $new = false;

                // if the video in db does not have a uuid saved, it will add it in both the db and on the file. This replaces any existing uuid on the file not known to the db.
                if (is_null($video->uuid)) {
                    EmbedUidInMetadata::dispatch($filePath, $uuid);
                    $video->update(['uuid' => $uuid]);
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
                    $ffprobe = FFMpegFFProbe::create();
                    $duration = floor($ffprobe
                        ->format($filePath) // extracts file information
                        ->get('duration'));
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

            } catch (\Throwable $th) {
                //throw $th;
                $ids = array_map(function ($transaction) {
                    return $transaction['id'];
                }, $transactions);

                dump('Error cannot verify file metadata ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates with IDs ' . [...$ids]);
                $error = true;
                break;
            }
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

            dump('Error cannot insert verified file metadata ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates with IDs ' . [...$ids]);
        }
    }
}
