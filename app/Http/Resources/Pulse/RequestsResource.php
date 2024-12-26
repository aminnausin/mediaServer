<?php


namespace App\Http\Resources\Pulse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use PauloHortelan\RequestsGraphPulse\Recorders\RequestsGraphRecorder;

class RequestsResource extends PulseResource {
    public function toArray(Request $request): array {
        [$requests, $time, $runAt] = $this->remember(fn() => $this->graph(
            [
                'informational',
                'successful',
                'redirection',
                'client_error',
                'server_error',
            ],
            'count',
        ));

        return [
            'requests' => [
                'requests' => $requests,
                'time' => $time,
                'runAt' => $runAt,
                $this->mergeWhen(
                    config('pulse-api.include_config'),
                    ['config' => [
                        'sample_rate' => Config::get('pulse.recorders.' . RequestsGraphRecorder::class . '.sample_rate'),
                        'record_informational' => Config::get('pulse.recorders.' . RequestsGraphRecorder::class . '.record_informational'),
                        'record_successful' => Config::get('pulse.recorders.' . RequestsGraphRecorder::class . '.record_successful'),
                        'record_redirection' => Config::get('pulse.recorders.' . RequestsGraphRecorder::class . '.record_redirection'),
                        'record_client_error' => Config::get('pulse.recorders.' . RequestsGraphRecorder::class . '.record_client_error'),
                        'record_server_error' => Config::get('pulse.recorders.' . RequestsGraphRecorder::class . '.record_server_error'),
                    ]]
                ),
            ],
        ];
    }
}
