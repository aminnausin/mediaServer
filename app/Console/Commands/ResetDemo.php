<?php

namespace App\Console\Commands;

use App\Models\Metadata;
use App\Models\Series;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

    protected $structureFiles = [
        'categories.json',
        'dataCache.json',
        'folders.json',
        'videos.json',
    ];

    /**
     * Execute the console command.
     */
    public function handle() {
        if (! app()->environment('demo')) {
            $this->error('Not running a demo environment');

            return;
        }

        if (! str_contains(strtolower(config('database.connections.pgsql.database', 'mediaServer')), 'demo')) {
            $this->error('Not using a demo database');

            return;
        }

        $this->info('Resetting demo database...');

        // Instead of deleting everything and rescanning every 15 minutes, just clear user editable data : Artisan::call('migrate:fresh', ['--force' => true]);

        $tables = ['users', 'tags', 'sessions', 'password_reset_tokens', 'personal_access_tokens'];

        foreach ($tables as $table) {
            DB::table($table)->delete();
            $this->info("✅ Cleared: $table.");

            try {
                // Attempt to find and reset the sequence on the 'id' column
                $seq = DB::selectOne('SELECT pg_get_serial_sequence(?, ?) AS seq', [$table, 'id'])->seq ?? null;

                if ($seq) {
                    DB::statement("ALTER SEQUENCE {$seq} RESTART WITH 1");
                }
            } catch (\Throwable $e) {
                // Silently skip if no 'id' is used
            }
        }

        $this->clearStructureFiles();

        Metadata::resetAllEditableFields();
        Series::resetAllEditableFields();

        Artisan::call('mediaServer:scan', ['library_id' => 1]);

        Artisan::call('db:seed', [
            '--class' => 'DemoSeeder',
            '--force' => true,
        ]);

        if (config('demo.auth_email') && config('demo.auth_password')) {
            User::updateOrCreate(
                ['email' => config('demo.auth_email')],
                [
                    'name' => 'Demo Admin',
                    'email_verified_at' => now(),
                    'password' => Hash::make(config('demo.auth_password')),
                ]
            );
        }

        $this->info('✅ Demo DB reset complete.');
    }

    private function clearStructureFiles() {
        $disk = Storage::disk('local');
        foreach ($this->structureFiles as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
                $this->info("✅ Deleted: $path");
            } else {
                $this->info("✅ Missing: $path");
            }
        }
    }
}
