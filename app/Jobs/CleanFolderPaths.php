<?php

namespace App\Jobs;

use App\Models\Folder;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CleanFolderPaths implements ShouldQueue {
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public $folders) {
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

        if (count($this->folders) == 0) {
            dump('Folder Data Lost');

            return;
        }

        $transactions = [];
        $error = false;
        foreach ($this->folders as $folder) {
            try {
                $stored = [];
                $changes = [];

                $stored = $folder->toArray();

                if (strpos($stored['path'], '\\')) {
                    $newPath = str_replace('\\\\', '/', $stored['path']); // Replace double back-slashes first
                    $newPath = str_replace('\\', '/', $newPath); // Replace single back-slashes
                    $changes['path'] = $newPath;
                }

                if (count($changes) > 0) {
                    array_push($transactions, [...$stored, ...$changes]);
                }
            } catch (\Throwable $th) {
                dump('Error cannot clean file path ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates');
                $error = true;
                break;
            }
        }

        if (count($transactions) == 0 || $error == true) {
            return;
        }
        Folder::upsert($transactions, 'id', ['path']);

        $msg = 'Updated ' . count($transactions) . ' folder path(s) from id ' . ($transactions[0]['id']) . ' to ' . ($transactions[count($transactions) - 1]['id']);
        dump($msg);

        $dataCache = Storage::json('dataCache.json') ?? [];
        $dataCache[date('Y-m-d-h:i:sa')] = [
            'job' => 'cleanFolderPaths',
            'message' => $msg,
            'data' => $transactions,
        ];
        Storage::put('dataCache.json', json_encode($dataCache, JSON_UNESCAPED_SLASHES));
    }
}
