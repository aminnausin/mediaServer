<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FolderResource;
use App\Models\Category;
use App\Models\Folder;
use Illuminate\Http\Request;

class PreviewController extends Controller {
    public function showPreview(Request $request, $dir = null) {
        // Parse the URL path
        $path = $request->path(); // e.g. category/folder?video=123
        // You’ll need logic to resolve this to the right category/folder/video

        $data = [
            'title' => 'Example Title',
            'description' => 'An auto-generated preview for bots.',
            'imageUrl' => 'https://yourcdn.com/cover.jpg',
            'url' => $request->fullUrl(),
        ];

        try {
            $category = Category::where('is_private', false)->oldest('id')->first();

            // If no category is found, redirect to /setup
            if (! $category) {
                return response()->view('og-preview', $data);
            }

            $folder = $category->default_folder_id ? Folder::find($category->default_folder_id) : $category->folders()->first();

            if (! $folder) {
                return response()->view('og-preview', $data);
            }

            $folderResource = new FolderResource($folder);

            $data['title'] = "$category->name · $folderResource->name";
            $data['description'] = $folderResource->series->description ?? $category->description ?? 'No Description';
            $data['imageUrl'] = $folderResource->series->thumbnail_url ? $folderResource->series->thumbnail_url : asset('storage/thumbnails/default.webp');
            $data['raw'] = $folderResource->series->thumbnail_url ? $folderResource->series->thumbnail_url : asset('storage/thumbnails/default.webp');

            return response()->view('og-preview', $data);
        } catch (\Throwable $th) {
            return response()->view('og-preview', [
                'title' => 'Example Title',
                'description' => 'An auto-generated preview for bots.',
                'imageUrl' => 'https://yourcdn.com/cover.jpg',
                'url' => $request->fullUrl(),
            ]);
        }
    }
}
