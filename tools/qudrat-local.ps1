[CmdletBinding(PositionalBinding = $false)]
param(
    [Parameter(Position = 0)]
    [ValidateSet('start', 'stop', 'artisan', 'test', 'serve', 'build')]
    [string]$Action = 'start',
    [Parameter(Position = 1, ValueFromRemainingArguments = $true)]
    [string[]]$CommandArguments,
    [string]$Php = "$env:USERPROFILE/.config/herd/bin/php83/php.exe",
    [string]$MySqlBin = 'C:/Program Files/MySQL/MySQL Server 8.0/bin'
)

$ErrorActionPreference = 'Stop'
$projectPath = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$runtimePath = Join-Path $projectPath 'storage/app/qudrat-local'
$dataPath = Join-Path $runtimePath 'mysql'
$port = 33317

# Never allow positional arguments to turn the PHP executable into a shell function.
if (!(Test-Path -LiteralPath $Php -PathType Leaf)) { throw 'The PHP executable path is invalid.' }

function Invoke-Checked([string]$Executable, [string[]]$Arguments) {
    & $Executable @Arguments
    if ($LASTEXITCODE -ne 0) { throw "$Executable exited with $LASTEXITCODE" }
}

if ($Action -eq 'start') {
    if (!(Test-Path -LiteralPath $Php) -or !(Test-Path -LiteralPath (Join-Path $MySqlBin 'mysqld.exe'))) {
        throw 'Pass -Php (PHP 8.2/8.3) and -MySqlBin pointing at installed native binaries.'
    }
    New-Item -ItemType Directory -Path $runtimePath -Force | Out-Null
    $iniPath = Join-Path $runtimePath 'php.ini'
    $extensionPath = Join-Path ([System.IO.Path]::GetDirectoryName($Php)) 'ext'
    $ini = @('memory_limit=512M', 'date.timezone=UTC', "extension_dir=`"$extensionPath`"")
    foreach ($extensionName in @('curl', 'fileinfo', 'gd', 'intl', 'mbstring', 'openssl', 'pdo_mysql', 'sodium', 'zip')) {
        $ini += "extension=$extensionName"
    }
    [System.IO.File]::WriteAllLines($iniPath, $ini)
    Invoke-Checked $Php @('-c', $iniPath, '-r', 'if (PHP_VERSION_ID < 80200 || PHP_VERSION_ID >= 80400) { fwrite(STDERR, "Use PHP 8.2/8.3 for the existing lockfile.\n"); exit(1); } echo PHP_VERSION, PHP_EOL;')

    # Start a NEW instance/datadir only. Never attach to an existing MySQL listener.
    if (Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue) {
        throw "Port $port is already in use. Stop this dedicated instance before starting again."
    }
    if (!(Test-Path -LiteralPath $dataPath)) {
        New-Item -ItemType Directory -Path $dataPath | Out-Null
        Invoke-Checked (Join-Path $MySqlBin 'mysqld.exe') @('--no-defaults', '--initialize-insecure', "--datadir=$dataPath", '--console')
    }
    $serverArguments = "--no-defaults --datadir=`"$dataPath`" --bind-address=127.0.0.1 --port=$port --mysqlx=0 --log-error=`"$runtimePath/mysql.log`" --pid-file=`"$runtimePath/mysql.pid`""
    $process = Start-Process -FilePath (Join-Path $MySqlBin 'mysqld.exe') -ArgumentList $serverArguments -WindowStyle Hidden -PassThru
    $ready = $false
    for ($attempt = 0; $attempt -lt 30; $attempt++) {
        & (Join-Path $MySqlBin 'mysqladmin.exe') --no-defaults --host=127.0.0.1 "--port=$port" --user=root ping --silent 2>$null | Out-Null
        if ($LASTEXITCODE -eq 0) { $ready = $true; break }
        if ($process.HasExited) { throw "Dedicated MySQL exited. See $runtimePath/mysql.log" }
        Start-Sleep -Milliseconds 500
    }
    if (!$ready) { throw 'Dedicated MySQL did not become ready.' }

    # Only this new instance is contacted; application credentials have not been read.
    $sql = "CREATE DATABASE IF NOT EXISTS yarub_qudrat_dev_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE IF NOT EXISTS yarub_qudrat_test_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER IF NOT EXISTS 'qudrat_local'@'127.0.0.1' IDENTIFIED BY 'qudrat-local-only'; GRANT ALL ON yarub_qudrat_dev_local.* TO 'qudrat_local'@'127.0.0.1'; GRANT ALL ON yarub_qudrat_test_local.* TO 'qudrat_local'@'127.0.0.1';"
    Invoke-Checked (Join-Path $MySqlBin 'mysql.exe') @('--no-defaults', '--host=127.0.0.1', "--port=$port", '--user=root', '--execute', $sql)

    $environmentFile = Join-Path $runtimePath '.env'
    if (!(Test-Path -LiteralPath $environmentFile)) {
        $example = Get-Content -LiteralPath (Join-Path $projectPath '.env.example')
        $values = [ordered]@{
            APP_NAME='YarubQudratLocal'; APP_ENV='local'; APP_DEBUG='false'; APP_URL='http://127.0.0.1:8000';
            APP_KEY=('base64:' + [Convert]::ToBase64String([System.Security.Cryptography.RandomNumberGenerator]::GetBytes(32)));
            DB_CONNECTION='mysql'; DB_HOST='127.0.0.1'; DB_PORT="$port"; DB_DATABASE='yarub_qudrat_dev_local';
            DB_USERNAME='qudrat_local'; DB_PASSWORD='qudrat-local-only'; DATABASE_URL=''; DB_SOCKET='';
            QUDRAT_ALLOW_DEVELOPMENT_IMPORT='true'; CACHE_DRIVER='array'; SESSION_DRIVER='array'; QUEUE_CONNECTION='sync';
            MAIL_MAILER='array'; FILESYSTEM_DISK='local'; BROADCAST_DRIVER='log'; LOG_CHANNEL='stderr'; LOG_DEPRECATIONS_CHANNEL='null';
            AWS_ACCESS_KEY_ID=''; AWS_SECRET_ACCESS_KEY=''; AWS_BUCKET=''; AWS_ENDPOINT=''; AWS_CLOUDFRONT_DOMAIN='';
            MOYASAR_KEY=''; MOYASAR_TEST_KEY=''; PUSHER_APP_ID=''; PUSHER_APP_KEY=''; PUSHER_APP_SECRET=''; DEBUGBAR_ENABLED='false'
        }
        $lines = @($example | Where-Object { $_ -notmatch '^[A-Z][A-Z0-9_]*=' })
        # Only non-live values are used; omit service settings from the example.
        foreach ($entry in $values.GetEnumerator()) { $lines += "$($entry.Key)=$($entry.Value)" }
        [System.IO.File]::WriteAllLines($environmentFile, $lines)
    }
    $testEnvironmentFile = Join-Path $runtimePath '.env.testing'
    if (!(Test-Path -LiteralPath $testEnvironmentFile)) {
        $testLines = Get-Content -LiteralPath $environmentFile
        $testLines = $testLines | Where-Object { $_ -notmatch '^(DB_|QUDRAT_EXISTING_LOCAL_DATABASE=|DATABASE_URL=)' }
        $testLines += @('DB_CONNECTION=mysql', 'DB_HOST=127.0.0.1', 'DB_PORT=33317', 'DB_DATABASE=yarub_qudrat_test_local', 'DB_USERNAME=qudrat_local', 'DB_PASSWORD=qudrat-local-only', 'DB_SOCKET=', 'DATABASE_URL=', 'QUDRAT_EXISTING_LOCAL_DATABASE=')
        [System.IO.File]::WriteAllLines($testEnvironmentFile, $testLines)
    }
    Write-Output "Dedicated native test MySQL ready on 127.0.0.1:$port. Review configuration: storage/app/qudrat-local/.env; test configuration: .env.testing"
} elseif ($Action -eq 'stop') {
    if (!(Test-Path -LiteralPath (Join-Path $runtimePath 'mysql.pid'))) { throw 'No dedicated MySQL PID file.' }
    $serverId = [int](Get-Content -LiteralPath (Join-Path $runtimePath 'mysql.pid'))
    $server = Get-CimInstance Win32_Process -Filter "ProcessId = $serverId"
    if (!$server -or $server.CommandLine -notlike "*$dataPath*" -or $server.CommandLine -notlike '*--port=33317*') {
        throw 'The PID does not identify the dedicated Qudrat instance.'
    }
    Invoke-Checked (Join-Path $MySqlBin 'mysqladmin.exe') @('--no-defaults', '--host=127.0.0.1', "--port=$port", '--user=root', 'shutdown')
} else {
    $iniPath = Join-Path $runtimePath 'php.ini'
    if (!(Test-Path -LiteralPath $iniPath)) { throw 'Run tools/qudrat-local.ps1 start first.' }
    Push-Location $projectPath
    try {
        if ($Action -eq 'serve') {
            & $Php -c $iniPath -S 127.0.0.1:8017 -t public tools/qudrat-router.php
        } elseif ($Action -eq 'build') {
            & node tools/qudrat-build.mjs
        } elseif ($Action -eq 'test') {
            & $Php -c $iniPath vendor/phpunit/phpunit/phpunit -c phpunit.qudrat.xml @CommandArguments
        } else {
            & $Php -c $iniPath tools/qudrat-local.php @CommandArguments
        }
        $commandExit = $LASTEXITCODE
    } finally {
        Pop-Location
    }
    exit $commandExit
}
