<?php

namespace App\Services\Puppeteer;

use Exception;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;

class ChromiumResolver {
    protected function canUseDocker(): bool {
        try {
            $dockerInfo = shell_exec('docker info --format "{{.ServerVersion}}" 2>&1');

            return ! empty($dockerInfo) && ! str_contains(strtolower($dockerInfo), 'error');
        } catch (\Throwable) {
            return false;
        }
    }

    protected function getPuppeteerChromiumPath(): ?string {
        try {
            // Determine user home directory
            $homeDir = getenv('HOME') ?: getenv('USERPROFILE');
            if (! $homeDir) {
                throw new ChromiumException('Unable to resolve home directory.');
            }

            // Chromium cache path
            $cacheDir = $homeDir . '/.cache/puppeteer/chrome';
            if (! is_dir($cacheDir)) {
                throw new ChromiumException("Chromium not found in Puppeteer cache: {$cacheDir}");
            }

            return $this->resolveChromiumBinary($cacheDir);
        } catch (\Throwable $th) {
            Log::warning('Puppeteer Chromium path not found', ['Error' => $th->getMessage()]);

            return null;
        }
    }

    protected function resolveChromiumBinary(string $baseDir): ?string {
        try {
            if (! is_dir($baseDir) || ! ($versions = glob($baseDir . '/*', GLOB_ONLYDIR)) || empty($versions[0])) {
                throw new ChromiumException('Invalid chromium binary query');
            }

            foreach ($versions as $versionDir) {
                $binary = match (PHP_OS_FAMILY) {
                    'Windows' => $versionDir . '/chrome-win64/chrome.exe',
                    'Darwin' => $versionDir . '/chrome-mac/Chromium.app/Contents/MacOS/Chromium',
                    default => $versionDir . '/chrome-linux64/chrome',
                };

                if (file_exists($binary)) {
                    return $binary;
                }
            }

            throw new ChromiumException('No chromium binary found');
        } catch (\Throwable) {
            return null;
        }
    }

    public function setChromiumBinary(Browsershot $browsershot): Browsershot {
        if (file_exists('/run/current-system/sw/bin/chromium')) {
            return $browsershot->setChromePath('/run/current-system/sw/bin/chromium');
        }

        if (file_exists('/usr/bin/chromium') || file_exists('/usr/bin/chromium-browser')) {
            return $browsershot->setChromePath('/usr/bin/chromium');
        }

        if ($this->canUseDocker()) {
            return $browsershot->useDocker();
        }

        if ($this->getPuppeteerChromiumPath()) {
            return $browsershot;
        }

        throw new ChromiumException('No Chromium or Docker available for Browsershot.');
    }
}

class ChromiumException extends Exception {}
