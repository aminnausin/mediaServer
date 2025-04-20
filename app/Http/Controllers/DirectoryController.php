<?php

namespace App\Http\Controllers;

use App\Http\Resources\FolderResource;
use App\Http\Resources\VideoResource;
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

            $folderList = $this->loadCategoryFolders($category->id);
            $data['dir']['folders'] = FolderResource::collection($folderList); // Full category data as http resources

            $folder = $resolver->resolveFolder($folderIdentifier, $folderList, $category); // Load folder
            $data = $this->loadFolderData($data, new FolderResource($folder));

            return $this->success($data, '', 200);
        } catch (ModelNotFoundException $e) {
            return $this->error([
                'categoryName' => $request->dir ?? '',
                'folderName' => $request->folderIdentifier ?? '',
            ], $e->getMessage(), 404);
        } catch (ForbiddenException $e) {
            return $this->error(null, $e->getMessage(), 403);
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to parse URL: ' . $th->getMessage(), 500);
        }
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
        $dirName = strtolower($category->name);

        if ((isset($privateCategories[$dirName]) || $category->is_private) && (! auth('sanctum')->check() || (Auth::user() && Auth::user()->id !== 1))) {
            throw new ForbiddenException('Access to this folder is forbidden');
        }
    }

    private function loadCategoryFolders(int $categoryId): Collection {
        return Folder::with('series')
            ->where('category_id', $categoryId)
            ->orderBy('name')
            ->get();
    }

    private function loadFolderData(array $data, FolderResource $folder): array {
        $folder->load(['videos.metadata.videoTags.tag']);

        $data['folder'] = [
            'id' => $folder->id,
            'name' => $folder->name,
            'videos' => VideoResource::collection($folder->videos),  // VideoResource::collection(Video::where('folder_id', $folderRaw->id)->get());
            'series' => $data['dir']['folders']
                ->firstWhere('id', $folder->id)
                ->series,
        ];

        return $data;
    }

    // private function parseCategory(string $dirName, ?array $selectQuery = ['id', 'name', 'default_folder_id', 'is_private']): Category {
    //     //TODO: Use Path Resolver to support name or id

    //     $query = Category::select(...$selectQuery);

    //     $category = Category::select(...$selectQuery)
    //         ->firstWhere('name', 'like', '%' . $dirName . '%');

    //     if ($category) {
    //         return $category;
    //     }

    //     if (!ctype_digit($dirName) || (int)$dirName <= 0) {
    //         throw new ModelNotFoundException("No category found with name matching '{$dirName}'");
    //     }

    //     try {
    //         return $query->findOrFail((int)$dirName);
    //     } catch (ModelNotFoundException $e) {
    //         throw new ModelNotFoundException(
    //             "No category found with name or ID matching '{$dirName}'",
    //             0,
    //             $e
    //         );
    //     }
    // }

    // private function parseFolder(array $data, Category $category): FolderResource {
    //     $folderIdentifier = trim($data['folder']['name'] ?? '');
    //     $folder = empty($folderIdentifier) ? $this->getDefaultFolder($data['dir']['folders'], $category) : $this->getFolderByName($data['dir']['folders'], $folderIdentifier);

    //     if (!empty($folderIdentifier)) {
    //         $folder = $this->getFolderByName($data['dir']['folders'], $folderIdentifier);
    //         if ($folder) {
    //             return $folder;
    //         }
    //     } else {
    //         return $this->getDefaultFolder($data['dir']['folders'], $category);
    //     }

    //     if ($folder) {
    //         return $folder;
    //     }

    //     if (!ctype_digit($folderIdentifier) || (int)$folderIdentifier <= 0) {
    //         throw new ModelNotFoundException("No folder found in category '{$category->name}' with name matching '{$folderIdentifier}'");
    //     }

    //     try {
    //         return Folder::where('category_id', $category->id)
    //             ->findOrFail($folderIdentifier);
    //     } catch (ModelNotFoundException $e) {
    //         throw new ModelNotFoundException(
    //             "No folder found in category '{$category->name}' with name or ID matching '{$folderIdentifier}'",
    //             0,
    //             $e
    //         );
    //     }
    // }

    // private function getDefaultFolder(ResourceCollection $folders, Category $category): FolderResource {
    //     return $category->default_folder_id
    //         ? $folders->firstWhere('id', $category->default_folder_id)
    //         : $folders->first();
    // }

    // private function getFolderByName(ResourceCollection $folders, string $folderName): ?FolderResource {
    //     //TODO: Use Path Resolver to support name or id
    //     $folderName = strtolower($folderName);

    //     return $folders->first(function ($folder) use ($folderName) {
    //         return strtolower($folder->name) === $folderName;
    //     }) ?? $folders->first(function ($folder) use ($folderName) {
    //         return str_contains(strtolower($folder->name), $folderName);
    //     });
    // }
}

class ForbiddenException extends Exception {}
