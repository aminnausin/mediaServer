<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Metadata;
use App\Services\External\LrcLibService;
use Illuminate\Http\Request;

class ExternalMetadataController extends Controller {
    public function __construct(protected LrcLibService $lrcLibService) {}

    public function importLyrics(Request $request, $id) {
        $metadata = Metadata::FindOrFail($id);

        return response()->json($this->lrcLibService->importLyrics($metadata));
    }

    public function searchLyrics(Request $request, $id) {
        $metadata = Metadata::FindOrFail($id);

        return response()->json($this->lrcLibService->searchLyrics($metadata, $request));
    }
}
