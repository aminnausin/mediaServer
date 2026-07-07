<?php

namespace App\Support;

class SeriesRules {
    public static function base(): array {
        return [
            'title' => 'required|max:255',
            'description' => 'nullable',
            'studio' => 'nullable|max:255',
            'rating' => RequestPresets::NON_NEGATIVE_INT . '|max:100',
            'seasons' => RequestPresets::NON_NEGATIVE_INT,
            'episodes' => RequestPresets::NON_NEGATIVE_INT,
            'films' => RequestPresets::NON_NEGATIVE_INT,
            'started_at' => 'nullable|date|date_format:Y-m-d',
            'ended_at' => 'nullable|date|date_format:Y-m-d',
            'avg_intro_duration' => 'min:0',
            'tags' => 'nullable|array',
            'tags.*.name' => 'required|min:1|max:64',
            'tags.*.id' => 'required|integer',
        ];
    }
}
