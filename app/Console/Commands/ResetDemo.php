<?php

namespace App\Console\Commands;

use App\Enums\ImageSource;
use App\Enums\ImageType;
use App\Models\Image;
use App\Models\Metadata;
use App\Models\Series;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

        $this->resetGeneratedPosters();

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

    /**
     * Resets the poster for `metadata` to the auto generated image if exists
     * Deletes all other images for `metadata` and `series` (including ogp images)
     */
    private function resetGeneratedPosters(): void {
        $nonGeneratedPosterImages = Image::where('image_source', '!=', ImageSource::GENERATED->value)->orWhere('image_type', ImageType::OGP->value)->get();

        $deletedImageIds = [];
        $deletedLog = [];

        foreach ($nonGeneratedPosterImages as $image) {
            try {
                Storage::disk('public')->delete($image->path);
                $deletedImageIds[] = $image->id;
                $deletedLog[] = ['id' => $image->id, 'imageable_id' => $image->imageable_id, 'type' => $image->image_type, 'source' => $image->image_source, 'source_url' => $image->source_url, 'path' => $image->path];
            } catch (\Throwable $e) {
                $this->warn("Could not delete file for image {$image->id}: {$e->getMessage()}");
            }
        }

        $deletedCount = count($deletedImageIds);

        Image::whereIn('id', $deletedImageIds)->delete();
        $this->info("✅ Deleted {$deletedCount} non-generated images from disk and DB.");
        if ($deletedCount > 0) {
            Log::info("Deleted {$deletedCount} non-generated images from disk and DB.", $deletedLog);
        }

        // might have to handle replaced auto generated posters somehow
        $generatedByMetadataUuid = Image::where('image_source', ImageSource::GENERATED->value)
            ->where('imageable_type', Metadata::class)
            ->where('image_type', ImageType::POSTER->value)
            ->get()
            ->groupBy('imageable_id');

        Metadata::query()->each(function (Metadata $metadata) use ($generatedByMetadataUuid) {
            $generatedId = $generatedByMetadataUuid->get($metadata->uuid)?->first()?->id;

            if ($generatedId && $metadata->primary_poster_id !== $generatedId) {
                $metadata->primary_poster_id = $generatedId;
                $metadata->save();
            }
        });

        $this->info('✅ Reset primary posters to generated images where available.');
    }
}
