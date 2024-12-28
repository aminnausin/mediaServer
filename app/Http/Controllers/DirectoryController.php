<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexFilesRequest;
use App\Http\Resources\FolderResource;
use App\Http\Resources\SeriesResource;
use App\Http\Resources\VideoResource;
use App\Jobs\CleanFolderPaths;
use App\Jobs\CleanVideoPaths;
use App\Jobs\IndexFiles;
use App\Jobs\SyncFiles;
use App\Jobs\VerifyFiles;
use App\Jobs\VerifyFolders;
use App\Models\Category;
use App\Models\Folder;
use App\Models\Video;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;

class DirectoryController extends Controller {
    use HttpResponses;

    public function showDirectoryAPI(Request $request) {
        // All this does is convert url to ids and names
        // IDEALLY it should also load data to prevent requiring more api requests
        // It does exactly that now it feels fast
        try {
            $privateCategories = ['legacy' => 1];
            $dir = trim(strtolower($request?->dir ?? ''));
            $folderName = trim(strtolower($request?->folderName ?? ''));

            if (isset($privateCategories[$dir]) && (! $request->user('sanctum')) || (Auth::user() && Auth::user()->id !== 1)) {
                $data['message'] = 'Unauthorized';

                return $this->error(null, 'Access to this folder is forbidden', 403);
            }

            $dirRaw = Category::select('id', 'default_folder_id')->firstWhere('name', 'ilike', '%' . $dir . '%');
            $data = ['dir' => ['id' => null, 'name' => $dir, 'folders' => null], 'folder' => ['id' => null, 'name' => $folderName ?? null, 'videos' => null]]; // Default null values

            if (! isset($dirRaw->id)) { // Cannot find category so return default nulls
                return $this->error(['categoryName' => $dir], 'Cannot find specified category', 404);
            }

            $folderList = Folder::where('category_id', $dirRaw->id)->withCount(['videos']); // Folders in category
            $data['dir'] = ['id' => $dirRaw->id, 'name' => $dir, 'folders' => FolderResource::collection($folderList->get())]; // Full category data
            $folderRaw = isset($request->folderName) ? $folderList->firstWhere('name', 'ilike', '%' . $folderName . '%') : (isset($dirRaw->default_folder_id) ? $folderList->firstWhere('id', $dirRaw->default_folder_id) : $folderList->first()); // Folder in request ? search by name else select first in category

            if (! isset($folderRaw->id)) { // no folder found
                return $this->error(['categoryName' => $dir, 'folderName' => $folderName], 'Cannot find folder in specified category', 404);
            }

            $videoList = VideoResource::collection(Video::where('folder_id', $folderRaw->id)->get());
            $data['folder'] = ['id' => $folderRaw->id, 'name' => $folderRaw->name, 'videos' => $videoList, 'series' => (isset($folderRaw->series) ? new SeriesResource($folderRaw->series) : null)];

            return $this->success($data, '', 200);
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to parse URL ' . $th->getMessage(), 500);
        }
    }

    public function scanFiles(Request $request, Category $category = null) {
        try {
            $chain = [
                new SyncFiles,
                new IndexFiles,
            ];

            $fileChunks = [];

            if ($category) {
                $videos = Video::whereHas('folder.category', function ($query) use ($category) {
                    $query->where('id', $category->id);
                })
                    ->with('folder.category')
                    ->orderBy('id')
                    ->get();
            } else {
                $videos = Video::orderBy('id')->get();
            }

            $videos->chunk(20, function ($videos) use (&$fileChunks) {
                $fileChunks[] = $videos;
            });

            foreach ($fileChunks as $chunk) {
                $chain[] = new VerifyFiles($chunk);
            }

            $folderChunks = [];
            if ($category) {
                $folders = Folder::whereHas('category', function ($query) use ($category) {
                    $query->where('id', $category->id);
                })
                    ->with('category')
                    ->orderBy('id')
                    ->get();
            } else {
                $folders = Folder::orderBy('id')->get();
            }

            $folders->chunk(20)->each(function ($chunk) use (&$folderChunks) {
                $folderChunks[] = $chunk;
            });

            foreach ($folderChunks as $chunk) {
                $chain[] = new VerifyFolders($chunk);
            }

            Bus::batch($chain)->dispatch();

            // dump('All jobs have been dispatched: Sync, Index, Verify Files, and Verify Folders.');
        } catch (\Throwable $th) {
            // dump('Error: cannot process the jobs');
            // dump($th);
            abort(500, $th->getMessage());
        }
    }

    public function indexFiles(Request $request, Category $category = null) {
        try {
            $chain = [
                new SyncFiles,
                new IndexFiles,
            ];
            Bus::batch($chain)->dispatch();
            dump('This job now uses ffprobe so it must be async');
        } catch (\Throwable $th) {
            dump('Error cannot index files');
            dump($th);
        }
    }

    public function syncFiles(Request $request) {
        try {
            SyncFiles::dispatchSync();
            dump('success');
        } catch (\Throwable $th) {
            dump('Error cannot sync files');
            dump($th);
        }
    }

    public function verifyFiles(Request $request, Category $category = null) {
        try {
            $jobs = [];
            $chunks = [];

            if ($category) {
                $videos = Video::whereHas('folder.category', function ($query) use ($category) {
                    $query->where('id', $category->id);
                })
                    ->with('folder.category')
                    ->orderBy('id')
                    ->get();
            } else {
                $videos = Video::orderBy('id')->get();
            }

            $videos->chunk(20, function ($videos) use (&$chunks) {
                $chunks[] = $videos;
            });

            foreach ($chunks as $chunk) {
                $jobs[] = new VerifyFiles($chunk);
                // break;
            }

            Bus::batch($jobs)->dispatch();
            dump('verifyFiles : This job has no web output. Check queue listener console for updates.');
        } catch (\Throwable $th) {
            dump('Error cannot verify file metadata');
            dump($th);
        }

        try {
            $jobs = [];
            $chunks = [];

            // Folder::orderBy('id')->chunk(20, function ($folders) use (&$chunks) {
            //     $chunks[] = $folders;
            // });

            $chunks = [];
            if ($category) {
                $folders = Folder::whereHas('category', function ($query) use ($category) {
                    $query->where('id', $category->id);
                })
                    ->with('category')
                    ->orderBy('id')
                    ->get();
            } else {
                $folders = Folder::orderBy('id')->get();
            }

            $folders->chunk(20)->each(function ($chunk) use (&$chunks) {
                $chunks[] = $chunk;
            });

            foreach ($chunks as $chunk) {
                $jobs[] = new VerifyFolders($chunk);
                // break;
            }

            Bus::batch($jobs)->dispatch();
            dump('verifyFolders : This job has no web output. Check queue listener console for updates.');
        } catch (\Throwable $th) {
            dump('Error cannot verify folder series data');
            dump($th);
        }
    }

    public function verifyFolders(Request $request, Category $category = null) {
        try {
            $jobs = [];
            $chunks = [];

            Folder::orderBy('id')->chunk(20, function ($folders) use (&$chunks) {
                $chunks[] = $folders;
            });

            foreach ($chunks as $chunk) {
                $jobs[] = new VerifyFolders($chunk);
                // break;
            }

            Bus::batch($jobs)->dispatch();
            dump('verifyFolders : This job has no web output. Check queue listener console for updates.');
        } catch (\Throwable $th) {
            dump('Error cannot verify folder series data');
            dump($th);
        }
    }

    public function cleanPaths() {
        $jobs = [];

        try {
            $chunks = [];

            Video::orderBy('id')->chunk(20, function ($videos) use (&$chunks) {
                $chunks[] = $videos;
            });

            foreach ($chunks as $chunk) {
                $jobs[] = new CleanVideoPaths($chunk);
                // break;
            }
        } catch (\Throwable $th) {
            dump('Error cannot clean video paths');
            dump($th);
        }

        try {
            $chunks = [];

            Folder::orderBy('id')->chunk(20, function ($folders) use (&$chunks) {
                $chunks[] = $folders;
            });

            foreach ($chunks as $chunk) {
                $jobs[] = new CleanFolderPaths($chunk);
                // break;
            }
        } catch (\Throwable $th) {
            dump('Error cannot clean folder paths');
            dump($th);
        }

        $jobs[] = new SyncFiles;
        Bus::batch($jobs)->dispatch();

        dump('This job has no web output. Check queue listener console for updates.');
    }
}
