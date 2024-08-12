<?php

namespace App\Jobs;

use App\Models\Video;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CleanVideoPaths implements ShouldQueue
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
                
                if(strpos($stored['path'], '\\')){
                    $newPath = str_replace('\\\\', '/', $stored['path']); // Replace double back-slashes first
                    $newPath = str_replace('\\', '/', $newPath); // Replace single back-slashes
                    $changes['path'] = $newPath;
                }

                if(count($changes) > 0){
                    array_push($transactions, [...$stored, ...$changes]);
                }
                
            } catch (\Throwable $th) {
                dump('Error cannot clean file path ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates');
                $error = true;
                break;
            }
        }

        if(count($transactions) == 0 || $error == true) return;
        Video::upsert($transactions, 'id', ['path']);

        $msg = 'Updated ' . count($transactions) . ' video path(s) from id ' . ($transactions[0]['id']) . ' to ' . ($transactions[count($transactions) - 1]['id']);
        dump($msg);

        $dataCache = Storage::json('public/dataCache.json') ?? array();
        $dataCache[date("Y-m-d-h:i:sa")] = array(
            "job"=>"cleanVideoPaths", 
            "message"=>$msg, 
            "data"=>$transactions, 
        );
        Storage::disk('public')->put('dataCache.json', json_encode($dataCache, JSON_UNESCAPED_SLASHES));
    }
}

