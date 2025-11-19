<?php

namespace App\Http\Controllers;

use App\Http\Resources\FolderResource;
use App\Models\Category;
use App\Models\Folder;
use App\Services\PathResolverService;
use App\Services\TaskService;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DirectoryController extends Controller {
    use HttpResponses;

    protected $taskService;

    protected $privateCategories = ['legacy' => 1];

    public function __construct(TaskService $taskService) {
        $this->taskService = $taskService;
    }

    public function showDirectoryAPI(Request $request, PathResolverService $resolver) {
        // All this does is convert url to ids and names
        // IDEALLY it should also load data to prevent requiring more api requests
        // It does exactly that now it feels fast
        try {
            $dir = $this->sanitizeInput($request->dir);
            $folderIdentifier = $this->sanitizeInput($request->folderName);

            $category = $resolver->resolveCategory($dir); // Load Category
            $this->validateCategoryAccess($category);

            $data = $this->buildBaseResponse($category, $folderIdentifier);  // Default null values

            // eager loads folders.series.foldertags.tag
            $folderList = $this->loadCategoryFolders($category->id);
            $data['dir']['folders'] = FolderResource::collection($folderList); // Full category data as http resources

            // eager loads folder.series if not sending a list of folders (does not apply in this case)
            $folder = $resolver->resolveFolder($folderIdentifier, $category, $folderList); // Load folder
            // eager loads folder.videos.metadata.videotags.tag and folder.series.foldertags.tag for a single folder
            $data = $this->loadFolderData($data, new FolderResource($folder));

            $result = $this->success($data, '', 200);
        } catch (ModelNotFoundException $e) {
            $result = $this->error([
                'categoryName' => $request->dir ?? '',
                'folderName' => $request->folderIdentifier ?? '',
            ], $e->getMessage(), 404);
        } catch (ForbiddenException $e) {
            $result = $this->error(null, $e->getMessage(), 403);
        } catch (\Throwable $th) {
            $result = $this->error(null, 'Unable to parse URL: ' . $th->getMessage(), 500);
        }

        return $result;
    }

    private function sanitizeInput(?string $input): string {
        return trim(strtolower($input ?? ''));
    }

    private function buildBaseResponse(Category $category, string $folderName): array {
        return [
            'dir' => [
                'id' => $category->id,
                'name' => $category->name,
                'folders' => null,
            ],
            'folder' => [
                'id' => null,
                'name' => $folderName,
                'videos' => null,
                'series' => null,
            ],
        ];
    }

    private function validateCategoryAccess(Category $category): void {
        if ($category->is_private && Auth::id() !== 1) {
            throw new ForbiddenException('Access to this folder is forbidden');
        }
    }

    private function loadCategoryFolders(int $categoryId): Collection {
        return Folder::with('series.folderTags.tag')
            ->where('category_id', $categoryId)
            ->orderBy('name')
            ->get();
    }

    private function loadFolderData(array $data, FolderResource $folder): array {
        $folder->load(['videos.metadata.videoTags.tag', 'series.folderTags.tag']);

        $request = Request::create('', 'GET', ['videos' => true]);
        $data['folder'] = $folder->toArray($request);

        return $data;
    }
}

class ForbiddenException extends Exception {}
