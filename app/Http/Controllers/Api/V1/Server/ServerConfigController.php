<?php

namespace App\Http\Controllers\Api\V1\Server;

use App\Http\Controllers\Controller;
use App\Http\Requests\Server\UpdateMediaConfigRequest;
use App\Http\Requests\Server\UpdatePerformanceConfigRequest;
use App\Http\Requests\Server\UpdateScannerConfigRequest;
use App\Http\Requests\Server\UpdateStorageConfigRequest;
use App\Http\Resources\Server\ServerConfigResource;
use App\Models\ServerConfig;
use App\Services\Server\QueueControlService;
use App\Services\Server\ServerConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ServerConfigController extends Controller {
    public function __construct(private ServerConfigService $config) {}

    public function index(): JsonResponse {
        Cache::forget('server_config:all');

        return response()->json(
            ServerConfigResource::collection(ServerConfig::all())->groupBy('group')->map(fn ($group) => $group->keyBy('key'))
        );
    }

    public function updateScanner(UpdateScannerConfigRequest $request) {
        $validated = $request->validated();

        $this->config->setMany($validated);

        return response()->noContent();
    }

    public function updatePerformance(UpdatePerformanceConfigRequest $request, QueueControlService $queueController) {
        $validated = $request->validated();

        $this->config->setMany($validated);
        $queueController->restart();

        return response()->noContent();
    }

    public function updateStorage(UpdateStorageConfigRequest $request) {
        $validated = $request->validated();

        $this->config->setMany($validated);

        return response()->noContent();
    }

    public function updateMedia(UpdateMediaConfigRequest $request) {
        $validated = $request->validated();

        $this->config->set('media_formats', $validated);

        return response()->noContent();
    }
}
