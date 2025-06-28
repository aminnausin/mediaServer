<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ResetDemo extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the app to a preconfigured demo state';

    /**
     * Execute the console command.
     */
    public function handle() {
        if (!str_contains(strtolower(config('database.connections.pgsql.database', 'mediaServer')), 'demo')) {
            $this->error("Not using a demo database");
            return;
        }
        $this->info('Resetting demo database...');

        Artisan::call('migrate:fresh', ['--force' => true]);
        Artisan::call('db:seed', [
            '--class' => 'DemoSeeder',
            '--force' => true
        ]);

        Artisan::call('mediaServer:scan', ['library_id' => 1]);

        $this->info('✅ Demo DB reset complete.');
    }
}
