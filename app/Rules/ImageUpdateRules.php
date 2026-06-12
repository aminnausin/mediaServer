<?php

namespace App\Rules;

class ImageUpdateRules {
    public static function base(): array {
        return [
            'image_id' => [
                'required_if:mode,existing',
                'integer',
                'exists:images,id',
            ],

            'image' => [
                'required_if:mode,upload',
                'file',
                'image',
                'mimes:' . implode(',', config('media.uploads.allowed_extentions', ['jpeg', 'jpg', 'png', 'webp'])),
                'max:' . config('media.uploads.max_upload_size', 10240),
            ],

            'url' => [
                'required_if:mode,url',
                'url',
                'max:2048',
            ],

            'deleted_images' => ['nullable', 'array'],
            'deleted_images.*' => ['integer'],
        ];
    }
}
