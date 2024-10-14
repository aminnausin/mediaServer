<?php

namespace App\Jobs;

use App\Models\Series;
use App\Models\Video;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerifyFolders implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public $folders)
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

        if (count($this->folders) == 0) {
            dump('Folder Data Lost');
            return;
        }

        $transactions = array();
        $error = false;
        foreach ($this->folders as $folder) {
            try {
                $stored = array();
                $changes = array();

                $series = Series::firstOrCreate(['composite_id' => $folder->path], ['folder_id' => $folder->id]);

                $stored = $series->toArray();

                if (is_null($series->episodes)) $changes['episodes'] = Video::where('folder_id', $folder->id)->count();

                if (is_null($series->title)) {
                    $changes['title'] = $folder->name;
                }

                if (count($changes) > 0) {
                    array_push($transactions, [...$stored, ...$changes]);
                    // dump([...$stored, ...$changes]);
                    // dump($changes);
                    // dump($folder->name);
                }
                // dump($series->toArray());

            } catch (\Throwable $th) {
                //throw $th;
                dump('Error cannot verify folder series data ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates');
                $error = true;
                break;
            }
        }

        try {
            if (count($transactions) == 0 || $error == true) return;
            Series::upsert($transactions, 'id', ['folder_id', 'title', 'episodes']);
            // Video::upsert($transactions, 'id', ['title','duration','season','episode','view_count']);
            dump('Updated ' . count($transactions) . ' folders from id ' . ($transactions[0]['folder_id']) . ' to ' . ($transactions[count($transactions) - 1]['folder_id']));
        } catch (\Throwable $th) {
            dump('Error cannot insert verified folder series data ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates');
        }
    }
}
