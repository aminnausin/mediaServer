param (
    [string]$Domain,
    [string]$IPAddress = "127.0.0.1"
)

if (-not $Domain) {
    $Domain = "app.test"
}

# Elevate if not already running as Administrator
if (-not ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")) {
    Start-Process powershell "-ExecutionPolicy Bypass -File `"$PSCommandPath`" -Domain `"$Domain`" -IPAddress `"$IPAddress`"" -Verb RunAs
    exit
}

$hostsPath = "$env:SystemRoot\System32\drivers\etc\hosts"

# Check if entry already exists
$exists = Get-Content $hostsPath | Where-Object { $_ -match "^\s*$IPAddress\s+$Domain" }

if (-not $exists) {
    Add-Content -Path $hostsPath -Value "`n$IPAddress`t$Domain"
    Write-Host "Added $Domain to hosts file." -ForegroundColor Green
} else {
    Write-Host "$Domain already exists in hosts file." -ForegroundColor Cyan
}

pause > $null
