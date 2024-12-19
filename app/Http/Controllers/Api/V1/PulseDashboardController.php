<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Pulse\DashboardResource;
use Illuminate\Http\Request;

class PulseDashboardController {
    public function index(Request $request) {
        $period = $request->query('period', '');

        return new DashboardResource(null, $period);
    }

    public function show(Request $request, string $type) {
        $period = $request->query('period', '');
        if (array_key_exists($type, config('pulse-api.resources')->toArray())) {
            return new (config('pulse-api.resources.' . $type))(null, $period);
        }

        return response()->json([
            'data' => [
                'message' => 'Metric type "' . $type . '" does not exist.',
            ],
        ], 404);
    }
}
