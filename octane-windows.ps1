$envFile = Join-Path $PSScriptRoot ".env"

$schemeValue = "http"
$hostValue = "127.0.0.1"
$portValue = "8080"

if (Test-Path $envFile) {
    Get-Content $envFile | ForEach-Object {
        if ($_ -match '^\s*APP_SCHEME\s*=\s*(.*?)\s*$') {
            $schemeValue = $Matches[1].Trim().Trim('"', "'")
        }
        if ($_ -match '^\s*APP_HOST\s*=\s*(.*?)\s*$') {
            $hostValue = $Matches[1].Trim().Trim('"', "'")
        }
        if ($_ -match '^\s*APP_PORT\s*=\s*(.*?)\s*$') {
            $portValue = $Matches[1].Trim().Trim('"', "'")
        }
    }
}

$urlValue = "${schemeValue}://${hostValue}:${portValue}"

$env:APP_SCHEME = $schemeValue
$env:APP_HOST = $hostValue
$env:APP_PORT = $portValue
$env:APP_URL = $urlValue


$phpArgs = @(
    'artisan', 'octane:start'
    '--server=frankenphp'
    "--host=$hostValue"
    "--port=$portValue"
    "--caddyfile=$(Join-Path $PSScriptRoot 'Caddyfile')"
    '--watch'
)

php @phpArgs
exit $LASTEXITCODE

# To make this work, this file must be edited manually to bypass linux specific startup requirements
# vendor/laravel/octane/src/Commands/Concerns/InteractsWithServers.php
# public function getSubscribedSignals(): array
# {
#     return \function_exists('pcntl_signal') ? [SIGINT, SIGTERM, SIGHUP] : [];
# }
