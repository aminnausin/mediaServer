<?php

namespace Tests\Unit\Services\Subtitles;

use App\Enums\SubtitleSource;
use App\Services\Subtitles\SubtitleScanner;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SubtitleScannerTest extends TestCase {
    private SubtitleScanner $scanner;

    protected function setUp(): void {
        parent::setUp();
        $this->scanner = new SubtitleScanner;
    }

    public function test_extracts_only_subtitle_streams_from_metadata() {
        $metadata = [
            'streams' => [
                ['codec_type' => 'video', 'index' => 0],
                ['codec_type' => 'subtitle', 'index' => 2, 'codec_name' => 'subrip'],
                ['codec_type' => 'audio', 'index' => 1],
            ],
        ];

        $streams = $this->scanner->filterSubtitleStreams($metadata);

        $this->assertCount(1, $streams);
        foreach ($streams as $stream) {
            $this->assertSame('subtitle', $stream['codec_type']);
        }
    }

    public function test_builds_subtitle_transactions_correctly() {
        $streams = [
            [
                'index' => 2,
                'codec_name' => 'subrip',
                'tags' => ['language' => 'eng', 'title' => 'fansub'],
                'disposition' => ['default' => 1, 'forced' => 0],
            ],
        ];

        $result = $this->scanner->buildSubtitleTransactions('uuid', $streams);

        $this->assertSame([
            [
                'metadata_uuid' => 'uuid',
                'track_id' => 2,
                'language' => 'eng',
                'title' => 'fansub',
                'codec' => 'subrip',
                'is_default' => true,
                'is_forced' => false,
                'external_path' => null,
                'source_key' => SubtitleSource::EMBEDDED->makeKey('2'),
            ],
        ], $result);
    }

    public function test_defaults_language_to_und_when_missing() {
        $streams = [
            [
                'index' => 3,
                'codec_name' => 'ass',
                'disposition' => [],
            ],
        ];

        $result = $this->scanner->buildSubtitleTransactions('uuid', $streams);

        $this->assertSame('und', $result[0]['language']);
    }

    public function test_skips_invalid_streams_and_logs_warning() {
        Log::spy();

        $streams = [
            ['codec_name' => 'subrip'],
        ];

        $result = $this->scanner->buildSubtitleTransactions('uuid', $streams);
        $this->assertEmpty($result);
        Log::shouldHaveReceived('warning')->once();
    }
}
