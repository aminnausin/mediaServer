<?php

namespace App\Http\Controllers\Api\V1\Server;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Folder;
use App\Models\Metadata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SetupController extends Controller {
    /**
     * Get setup status (existance of data)
     */
    public function setupStatus(Request $request) {
        $stats = Cache::remember('setup_stats', 300, function () {
            return [
                'library_count' => Category::count(),
                'folder_count' => Folder::count(),
                'media_count' => Metadata::count(),
            ];
        });

        if ($stats['library_count'] == 0) {
            return response()->json([
                'has_data' => false,
                ...$stats,
                'default_library' => null,
            ]);
        }

        $defaultLibrary = Category::orderBy('name')
            ->when(! $request->user()?->isAdmin(), function ($query) {
                $query->where('is_private', false);
            })
            ->first();

        return response()->json([
            'has_data' => $stats['library_count'] > 0,
            'default_library' => [
                'id' => $defaultLibrary?->id,
                'name' => $defaultLibrary?->name,
            ],
            ...$stats,
        ]);
    }
}
