<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\FileJobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobController extends Controller {
    private const DEFAULT_ERROR_PREFIX = 'Error cannot ';

    public function __construct(protected FileJobService $fileJobService) {}

    public function scanFiles(Request $request, ?Category $category = null) {
        return $this->handleJobRequest(
            request: $request,
            method: 'scanFiles',
            jobName: 'SCAN FILES',
            category: $category,
        );
    }

    public function indexFiles(Request $request) {
        return $this->handleJobRequest(
            request: $request,
            method: 'indexFiles',
            jobName: 'INDEX FILES',
        );
    }

    public function syncFiles(Request $request) {
        return $this->handleJobRequest(
            request: $request,
            method: 'syncFiles',
            jobName: 'SYNC FILES',
        );
    }

    public function verifyFiles(Request $request, ?Category $category = null) {
        return $this->handleJobRequest(
            request: $request,
            method: 'verifyFiles',
            jobName: 'VERIFY FILES',
            category: $category,
        );
    }

    public function verifyFolders(Request $request, ?Category $category = null) {
        return $this->handleJobRequest(
            request: $request,
            method: 'verifyFolders',
            jobName: 'VERIFY FOLDERS',
            category: $category,
        );
    }

    public function cleanPaths(Request $request): JsonResponse {
        return $this->handleJobRequest(
            request: $request,
            method: 'cleanPaths',
            jobName: 'CLEAN PATHS',
        );
    }

    /**
     * @param  'scanFiles'|'indexFiles'|'syncFiles'|'verifyFiles'|'verifyFolders'|'cleanPaths'  $method
     */
    protected function handleJobRequest(
        Request $request,
        string $method,
        string $jobName,
        ?Category $category = null,
        ?string $errorMessage = null,
    ): JsonResponse {
        try {
            if (! method_exists($this->fileJobService, $method)) {
                return response()->json([
                    'error' => 'Invalid job method',
                    'details' => "Method $method not available",
                ], 400);
            }

            $data = ['userId' => $request->user()?->id];
            $task = $category
                ? $this->fileJobService->$method($data, $category)
                : $this->fileJobService->$method($data);

            return response()->json([
                'task_id' => $task->id,
                'message' => "Async Task \"$jobName\" was started.",
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $errorMessage ?? self::DEFAULT_ERROR_PREFIX . strtolower($jobName),
                'details' => $th->getMessage(),
            ], 500);
        }
    }
}
