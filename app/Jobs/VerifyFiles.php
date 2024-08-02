<?php

namespace App\Jobs;

use App\Models\Record;
use App\Models\Video;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use FFMpeg\FFProbe as FFMpegFFProbe;
use Illuminate\Support\Facades\Storage;

class VerifyFiles implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public $videos)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...
            return;
        }

        if(count($this->videos) == 0){
            dump('Video Data Lost');
            return;
        }

        $transactions = array();
        $error = false;
        foreach ($this->videos as $video) {
            try {
                $stored = array();
                $changes = array();

                $stored = $video->toArray();

                preg_match('![sS][0-9]+!', $stored['name'], $seasonRaw);
                preg_match('![eE][0-9]+!', $stored['name'], $episodeRaw);
                preg_match('![0-9]+!', $seasonRaw[0] ?? '', $season);
                preg_match('![0-9]+!', $episodeRaw[0] ?? '', $episode);


                if(is_null($video->duration)){
                    // dump(Storage::path('') . 'public\\' . substr($video->path, 8));
                    $ffprobe = FFMpegFFProbe::create();
                    $duration = floor($ffprobe
                        ->format(Storage::path('') . 'public\\' . substr($video->path, 8)) // extracts file informations
                        ->get('duration'));   
                    $changes['duration'] = $duration;
                }

                if(is_null($video->episode) && count($episode) == 1) $changes['episode'] = (int)$episode[0];
                if(is_null($video->season) && count($season) == 1) $changes['season'] = (int)$season[0];

                if(is_null($video->title)){
                    $newTitle = count($season) == 1 ? 'S' . $season[0] : '';
                    $newTitle .= count($episode) == 1 ? 'E' . $episode[0] : '';

                    if($newTitle != '') $changes['title'] = $newTitle;
                } 

                is_null($video->view_count) ? $changes['view_count'] = Record::where('video_id', $video->id)->count() : $stored['view_count'] = $video->view_count;

                if(count($changes) > 0){
                    array_push($transactions, [...$stored, ...$changes]);
                    // dump([...$stored, ...$changes]);
                    // dump($changes);
                    // dump($video->name);
                }
                
            } catch (\Throwable $th) {
                //throw $th;
                dump('Error cannot verify file metadata ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates');
                $error = true;
                break;
            }
        }

        if(count($transactions) == 0 || $error == true) return;
        Video::upsert($transactions, 'id', ['title','duration','season','episode','view_count']);
        dump('Updated ' . count($transactions) . ' videos from id ' . ($transactions[0]['id']) . ' to ' . ($transactions[count($transactions) - 1]['id']));
    }
}

