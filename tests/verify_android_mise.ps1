$ErrorActionPreference = 'Stop'

$repo = Split-Path -Parent $PSScriptRoot
$mise = Get-Content (Join-Path $repo 'mise.toml') -Raw
$androidTaskfile = Get-Content (Join-Path $repo 'android/Taskfile.yml') -Raw
$setupFiles = @(
    (Join-Path $repo 'mise-tasks/android/setup.ps1'),
    (Join-Path $repo 'mise-tasks/android/setup.sh')
)

function Assert-Contains([string]$text, [string]$needle, [string]$message) {
    if ($text -notmatch [regex]::Escape($needle)) {
        throw $message
    }
}

Assert-Contains $mise 'java = "17"' 'mise.toml must select Java 17.'
Assert-Contains $mise 'android-sdk = "23.0"' 'mise.toml must select Android command-line tools 23.0.'
Assert-Contains $mise '"aqua:Genymobile/scrcpy" = "5.0.1"' 'mise.toml must select scrcpy through aqua.'
Assert-Contains $androidTaskfile 'mise run android:setup' 'android/Taskfile.yml must wrap mise run android:setup.'

$setupMode = git ls-files --stage -- 'mise-tasks/android/setup.sh'
if ($setupMode -notmatch '^100755 ') {
    throw 'mise-tasks/android/setup.sh must be executable for Unix mise file-task discovery.'
}

foreach ($setupFile in $setupFiles) {
    if (-not (Test-Path $setupFile)) {
        throw "Missing Android mise setup task: $setupFile"
    }
    $task = Get-Content $setupFile -Raw
    foreach ($package in @('platform-tools', 'platforms/android-37.0', 'build-tools/37.0.0')) {
        Assert-Contains $task $package "$setupFile must install $package."
    }
    Assert-Contains $task '--licenses' "$setupFile must leave license acceptance interactive."
    if ($task -match '(?im)(yes[ ]*[|]|[|][ ]*yes|echo[ ]+y|Set-Content.*license)') {
        throw "$setupFile must not automate Android license acceptance."
    }
}

Write-Output 'Android mise configuration checks passed.'
