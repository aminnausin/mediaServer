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

    public function test_returns_output_path_on_success() {
        Storage::fake('local');
        Storage::disk('local')->put('in.vtt', 'data');

        $result = $this->formatter->convert('in.vtt', 'out.vtt', 'vtt');

        $this->assertSame('out.vtt', $result);
        $this->assertFileExists(Storage::disk('local')->path('out.vtt'));
    }
}
