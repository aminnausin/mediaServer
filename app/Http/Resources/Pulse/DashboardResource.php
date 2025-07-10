<?php

namespace App\Http\Resources\Pulse;

use Illuminate\Http\Request;

class DashboardResource extends PulseResource {
    public function toArray(Request $request): array {
        $resources = config('pulse-api.resources');

        $result = $resources->mapWithKeys(function (string $resource) use ($request) {
            return (new $resource(null, $this->period))->toArray($request);
        });

        return $result->toArray();
    }
}
