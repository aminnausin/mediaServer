<?php

namespace App\Http\Controllers\Api\V1\Server;

use App\Http\Controllers\Controller;
use App\Http\Requests\Server\UpdateMediaConfigRequest;
use App\Http\Requests\Server\UpdatePerformanceConfigRequest;
use App\Http\Requests\Server\UpdateScannerConfigRequest;
use App\Http\Requests\Server\UpdateStorageConfigRequest;
use App\Models\ServerConfig;
use App\Services\Server\QueueControlService;
use App\Services\Server\ServerConfigService;
use Illuminate\Http\JsonResponse;

class ServerConfigController extends Controller {
    public function __construct(private ServerConfigService $config) {}

    public function index(): JsonResponse {
        $configs = ServerConfig::all()->groupBy('group');

        return response()->json(
            [
                'values' => $configs->map(fn ($group) => $group->pluck('value', 'key')),
                'defaults' => $configs->map(fn ($group) => $group->pluck('default_value', 'key')),
            ]
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

        $this->config->set('supported_extentions', $validated['supported_extentions']);

        return response()->noContent();
    }
}
