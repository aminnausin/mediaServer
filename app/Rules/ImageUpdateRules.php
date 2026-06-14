<?php

namespace App\Rules;

class ImageUpdateRules {
    public static function base(): array {
        return [
            'image_id' => [
                'required_if:mode,existing',
                'integer',
            ],

            'image' => [
                'required_if:mode,upload',
                'file',
                'image',
                'mimes:' . implode(',', config('media.uploads.allowed_extensions', ['jpeg', 'jpg', 'png', 'webp'])),
                'max:' . config('media.uploads.max_size_kb', 10240),
            ],

            'url' => [
                'required_if:mode,url',
                'url:http,https',
                'max:2048',
            ],

            'deleted_images' => ['nullable', 'array'],
            'deleted_images.*' => ['integer'],
        ];
    }
}
