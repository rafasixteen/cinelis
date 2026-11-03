$ErrorActionPreference = 'Stop'

$sdkRoot = $env:ANDROID_HOME
if ([string]::IsNullOrWhiteSpace($sdkRoot)) {
    $sdkRoot = $env:ANDROID_SDK_ROOT
}
if ([string]::IsNullOrWhiteSpace($sdkRoot)) {
    throw 'ANDROID_HOME or ANDROID_SDK_ROOT must be set by mise before running android:setup.'
}

& sdkmanager "--sdk_root=$sdkRoot" 'platform-tools' 'platforms;android-37' 'build-tools;37.0.0'
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}

Write-Output 'Android SDK licenses are interactive; answer them in the prompt.'
& sdkmanager "--sdk_root=$sdkRoot" '--licenses'
exit $LASTEXITCODE
