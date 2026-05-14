<?php

namespace App\Console\Commands;

use App\Services\Server\ServerConfigService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ConfigureHorizon extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'horizon:configure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load Horizon worker config from database to config file';

    /**
     * Execute the console command.
     */
    public function handle(ServerConfigService $config) {
        if (! app()->environment('production')) {
            $this->warn('Only affects production environments');

            return 0;
        }

        $maxScanWorkers = $config->get('max_scan_workers', 10);
        $maxEventWorkers = $config->get('max_event_workers', 5);

        $content = "<?php\n\nreturn [\n";
        $content .= "    'max_scan_workers' => {$maxScanWorkers},\n";
        $content .= "    'max_event_workers' => {$maxEventWorkers},\n";
        $content .= "];\n";

        $path = config_path('horizon-dynamic.php');
        File::put($path, $content);

        $this->info("Horizon config loaded: scan={$maxScanWorkers}, event={$maxEventWorkers}");
        $this->call('config:clear');

        return 0;
    }
}
