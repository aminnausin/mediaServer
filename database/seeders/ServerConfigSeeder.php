<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServerConfigSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $configs = [
            [
                'key' => 'media_formats',
                'value' => config('media.format_map'),
                'default_value' => config('media.format_map'),
                'type' => 'array',
                'group' => 'media',
            ],
            [
                'key' => 'max_scan_workers',
                'value' => 10,
                'default_value' => 10,
                'type' => 'integer',
                'group' => 'performance',
            ],
            [
                'key' => 'max_event_workers',
                'value' => 5,
                'default_value' => 5,
                'type' => 'integer',
                'group' => 'performance',
            ],
            [
                'key' => 'uuid_embed',
                'value' => true,
                'default_value' => true,
                'type' => 'bool',
                'group' => 'scanner',
            ],
            [
                'key' => 'uuid_write_cache',
                'value' => false,
                'default_value' => false,
                'type' => 'bool',
                'group' => 'scanner',
            ],
            [
                'key' => 'attachments_extract',
                'value' => false,
                'default_value' => false,
                'type' => 'bool',
                'group' => 'scanner',
            ],
            [
                'key' => 'art_extract',
                'value' => true,
                'default_value' => true,
                'type' => 'bool',
                'group' => 'scanner',
            ],
            [
                'key' => 'thumbnails_generate',
                'value' => false,
                'default_value' => false,
                'type' => 'bool',
                'group' => 'scanner',
            ],
            [
                'key' => 'thumbnails_auto_download',
                'value' => true,
                'default_value' => true,
                'type' => 'bool',
                'group' => 'scanner',
            ],
            [
                'key' => 'cache_path',
                'value' => null,
                'default_value' => 'storage/cache',
                'type' => 'string',
                'group' => 'storage',
            ],
            [
                'key' => 'metadata_path',
                'value' => null,
                'default_value' => 'storage/metadata',
                'type' => 'string',
                'group' => 'storage',
            ],
        ];

        foreach ($configs as &$config) {
            if ($config['type'] === 'array') {
                $config['value'] = json_encode($config['value']);
                $config['default_value'] = json_encode($config['default_value']);
            }
            $config['created_at'] = $config['updated_at'] = now();
        }

        DB::table('server_configs')->insertOrIgnore($configs);
    }
}
