<?php

namespace App\Support;

class MetadataRules {
    public static function base(): array {
        return [
            'title' => 'required|max:255',
            'description' => 'nullable',
            'lyrics' => 'nullable',
            'artist' => 'nullable|max:255',
            'album' => 'nullable|max:255',
            'episode' => RequestPresets::NON_NEGATIVE_INT,
            'season' => RequestPresets::NON_NEGATIVE_INT,
            'poster_url' => 'nullable|url',
            'released_at' => 'nullable|date|date_format:Y-m-d',
            'tags' => 'nullable|max:128',
            'intro_start' => 'nullable|min:0',
            'intro_duration' => 'min:0',
            'video_tags' => 'nullable|array',
            'video_tags.*.name' => 'required|min:1|max:64',
            'video_tags.*.id' => 'required|integer',
        ];
    }
}
