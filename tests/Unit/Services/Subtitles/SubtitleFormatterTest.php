<?php

namespace Tests\Unit\Services\Subtitles;

use App\Services\Subtitles\SubtitleFormatter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubtitleFormatterTest extends TestCase {
    private SubtitleFormatter $formatter;

    protected function setUp(): void {
        parent::setUp();
        $this->formatter = new SubtitleFormatter;
    }

    public function test_throws_if_input_missing() {
        Storage::fake('local');

        $this->expectException(\RuntimeException::class);
        $this->formatter->convert('missing.vtt', 'out.vtt', 'vtt');
    }

    public function test_throws_for_unsupported_format() {
        Storage::fake('local');
        Storage::disk('local')->put('in.srt', 'data');

        $this->expectException(\InvalidArgumentException::class);
        $this->formatter->convert('in.srt', 'out.foo', 'foo');
    }

    // Idk how to do a success test since the default strategy or any strategy would include running ffmpeg which makes no sense
}
