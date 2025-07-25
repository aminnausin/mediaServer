<?php

namespace Database\Seeders;

use App\Models\Metadata;
use App\Models\Record;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        User::firstOrCreate(
            ['email' => 'demo@mediaserver.me'],
            [
                'name' => 'demo',
                'email_verified_at' => now(),
                'password' => Hash::make('mediaserver'),
            ]
        );
        User::factory(5)->create();
        Tag::factory(15)->create();

        Metadata::inRandomOrder()->limit(10)->get()->each(function ($metadata) {
            Record::factory()->count(2)->create([
                'user_id' => 1,
                'metadata_id' => $metadata->id,
            ]);

            $metadata->syncViewCountToRecords();
        });
    }
}
