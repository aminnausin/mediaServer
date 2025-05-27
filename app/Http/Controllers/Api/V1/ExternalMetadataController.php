<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Metadata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExternalMetadataController extends Controller {
    public function importLyrics(Request $request, $id) {
        $metadata = Metadata::FindOrFail($id);
        $data = [
            'artist_name' => explode(' - ', $metadata->description)[0],
            'track_name' => $metadata->title,
            // 'album_name' => $request->query('album_name'),
            'duration' => $metadata->duration,
        ];
        $response = Http::get('https://lrclib.net/api/get', $data);

        return response()->json(['lrclib' => $response->json(), 'payload' => $data]);
    }
}
