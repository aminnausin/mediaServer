<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateReverbKeys extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reverb:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate REVERB_APP_ID, REVERB_APP_KEY, and REVERB_APP_SECRET and insert them into the .env file';

    /**
     * Execute the console command.
     */
    public function handle() {
        $reverbAppId = random_int(100000, 999999);
        $reverbAppKey = bin2hex(random_bytes(16));
        $reverbAppSecret = bin2hex(random_bytes(16));

        $this->setEnvironmentValue('REVERB_APP_ID', $reverbAppId);
        $this->setEnvironmentValue('REVERB_APP_KEY', $reverbAppKey);
        $this->setEnvironmentValue('REVERB_APP_SECRET', $reverbAppSecret);

        $this->info('Reverb keys generated and updated in .env file.');
    }

    // Helper method to set the values in the .env file
    protected function setEnvironmentValue($key, $value) {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);
        $pattern = "/^$key=[^\n]*/m";
        $replacement = "$key=$value";

        $newContent = preg_replace($pattern, $replacement, $content);

        if ($newContent !== null) {
            file_put_contents($envPath, $newContent);
        }
    }
}
