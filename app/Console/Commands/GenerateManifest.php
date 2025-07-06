<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateManifest extends Command {
    protected $signature = 'app:manifest';

    protected $description = 'Generate the current Git version and commit hash to a manifest';

    public function handle() {
        $commit = trim(shell_exec('git rev-parse --short HEAD') ?? '')
            ?: substr(env('GITHUB_SHA', ''), 0, 7)
            ?: 'unknown';

        $version = trim(shell_exec('git describe --tags --abbrev=0') ?? '')
            ?: (file_exists(base_path('VERSION')) ? trim(file_get_contents(base_path('VERSION'))) : null)
            ?: env('GITHUB_REF_NAME', 'unknown');

        $data = json_encode([
            'version' => $version,
            'commit' => $commit,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'os' => php_uname('s') . ' ' . php_uname('v'),
            'cpu_arch' => php_uname('m'),
            'cpu_cores' => trim(shell_exec('nproc') ?? '') ?: 'unknown',
            'login_message' => config('app.login_message'),
        ], JSON_PRETTY_PRINT);

        try {
            File::put(public_path('manifest.json'), $data);
            $this->info("App manifest generated: $version ($commit)");
        } catch (\Throwable $e) {
            $this->error('❌ Failed to write manifest: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
