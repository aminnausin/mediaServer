<?php

namespace App\Jobs;

use App\Models\Record;
use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use FFMpeg\FFProbe as FFMpegFFProbe;

class VerifyFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        Video::orderBy('id')->chunk(100, function ($videos) {
            $transactions = array();
            foreach ($videos as $video) {
                try {
                    $stored = array();
                    $changes = array();

                    $stored = $video->toArray();

                    preg_match('![sS][0-9]+!', $stored['name'], $seasonRaw);
                    preg_match('![eE][0-9]+!', $stored['name'], $episodeRaw);
                    preg_match('![0-9]+!', $seasonRaw[0] ?? '', $season);
                    preg_match('![0-9]+!', $episodeRaw[0] ?? '', $episode);


                    if(is_null($video->duration)){
                        $ffprobe = FFMpegFFProbe::create();
                        $duration = ceil($ffprobe
                            ->format($video->path) // extracts file informations
                            ->get('duration'));   
                        $changes['duration'] = $duration;
                    }


                    if(is_null($video->season) && count($season) == 1) $changes['season'] = (int)$season;
                    if(is_null($video->episode) && count($episode) == 1) $changes['episode'] = (int)$episode;
                    if(is_null($video->season) && count($season) == 1) $changes['season'] = (int)$season;

                    if(is_null($video->title)){
                        $newTitle = count($season) == 1 ? 'S' . $season[0] : '';
                        $newTitle .= count($episode) == 1 ? 'E' . $episode[0] : '';

                        if($newTitle != '') $changes['title'] = $newTitle;
                    } 

                    is_null($video->view_count) ? $changes['view_count'] = Record::where('video_id', $video->id)->count() : $stored['view_count'] = $video->view_count;


                    // $video->update($changes);
                    if(count($changes) > 0){
                        array_push($transactions, [...$stored, ...$changes]);
                        dump([...$stored, ...$changes]);
                        dump($changes);
                    }
                    
                } catch (\Throwable $th) {
                    //throw $th;
                    dump('errir ' . $th->getMessage());
                    break;
                }
            }

            Video::upsert($transactions, 'id', ['title','duration','season','episode','view_count']);
        });
    }
}

