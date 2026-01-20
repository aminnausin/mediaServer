<?php

namespace App\Http\Controllers\Api\V1\Metadata;

use App\Http\Controllers\Controller;
use App\Models\Metadata;
use App\Services\Subtitles\SubtitleResolver;
use Illuminate\Http\Request;

class SubtitleController extends Controller {
    public function __construct(protected SubtitleResolver $subtitleResolver) {}

    /**
     * Look for a specified subtitle resource.
     *
     * Subtitle Extraction Process ->
     *      Initially scan file, set subtitles_scanned_at time and make partially identifying subtitle rows in the subtitle table
     *      then when requested by stream or file type, if the exact requested file matches an existing one, return that but if not probe again,
     *      and for each found stream, extract it, write to a file and update the row in the subtitle file, and then convert it to the requested format and return that file
     *      on subsequent requests, a matching file (usually vtt) will already exist and get returned directly
     *
     * Route: /data/subtitles/{metadata:uuid}/{track}.{format?}
     */
    public function show(
        Request $request,
        Metadata $metadata,
        int $track, // track number to specify file like 2.vtt or 3.vtt
        string $format = 'vtt'
    ) {
        // TODO: Log activity from request (requires future activity feature)
        return $this->subtitleResolver->resolveSubtitles($metadata, $track, $format);
    }

    public function showExternalTrack(
        Request $request,
        Metadata $metadata,
        string $language,
        string $format = 'vtt'
    ) {
        return $this->subtitleResolver->resolveSubtitles($metadata, 0, $format, $language);
    }

    // TODO: Add an API route that gets subtitles for a specific metadata or video row and cache the results. These are only used to determine the filepath used in the <track/>
}
