param(
    [ValidateSet('serve','test','build','migrate','admin','content')]
    [string]$Action = 'serve'
)
$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$phpPath = 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe'
if (-not (Test-Path -LiteralPath $phpPath)) {
    $phpPath = (Get-Command php -ErrorAction Stop).Source
}
Push-Location -LiteralPath $projectRoot
try {
    switch ($Action) {
        'serve' { & $phpPath artisan serve --host=127.0.0.1 --port=8000 }
        'test' { & $phpPath vendor/phpunit/phpunit/phpunit --configuration phpunit.xml }
        'build' { & npm.cmd run build }
        'migrate' {
            & $phpPath artisan config:clear
            if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
            & $phpPath artisan migrate --seed
        }
        'admin' { & $phpPath artisan dibitara:admin }
        'content' { & $phpPath artisan dibitara:install-content }
    }
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
} finally {
    Pop-Location
}
