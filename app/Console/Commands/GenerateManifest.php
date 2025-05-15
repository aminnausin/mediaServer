<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateManifest extends Command {
    protected $signature = 'app:manifest';

    protected $description = 'Generate the current Git version and commit hash to a manifest';

    public function handle() {
        $version = trim(shell_exec('git describe --tags --abbrev=0')) ?: '0.0.0b';
        $commit = trim(shell_exec('git rev-parse --short HEAD')) ?: 'unknown';

        $data = json_encode([
            'version' => $version,
            'commit' => $commit,
        ], JSON_PRETTY_PRINT);

        File::put(storage_path('app/manifest.json'), $data);

        $this->info("App manifest generated: $version ($commit)");
    }
}
