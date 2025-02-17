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
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            $this->error("The env file ($envPath) does not exist.");

            return false;
        }

        if ($this->keysExist()) {
            $this->info('Reverb keys already exist.');

            return false;
        }

        $reverbAppId = random_int(100000, 999999);
        $reverbAppKey = bin2hex(random_bytes(16));
        $reverbAppSecret = bin2hex(random_bytes(16));

        $this->setEnvironmentValue('REVERB_APP_ID', $reverbAppId, $envPath);
        $this->setEnvironmentValue('REVERB_APP_KEY', $reverbAppKey, $envPath);
        $this->setEnvironmentValue('REVERB_APP_SECRET', $reverbAppSecret, $envPath);

        $this->info('Reverb keys generated and updated in .env file.');
    }

    // Helper method to set the values in the .env file
    protected function setEnvironmentValue($key, $value, $envPath) {
        $content = file_get_contents($envPath);
        $pattern = "/^$key=[^\n]*/m";
        $replacement = "$key=$value";

        if (preg_match($pattern, $content)) {
            $newContent = preg_replace($pattern, $replacement, $content);
        } else {
            $newContent = $content . PHP_EOL . "$key=$value";
        }

        if ($newContent !== null) {
            file_put_contents($envPath, $newContent);
        }
    }

    protected function keysExist() {
        $reverbAppId = (int) env('REVERB_APP_ID');
        $reverbAppKey = env('REVERB_APP_KEY', '');
        $reverbAppSecret = env('REVERB_APP_SECRET', '');

        if ($reverbAppId < 100000 || $reverbAppId > 999999) {
            return false;
        }

        if (strlen($reverbAppKey) < 20 || strlen($reverbAppSecret) < 20) {
            return false;
        }

        return true;
    }
}
