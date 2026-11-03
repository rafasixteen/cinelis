#!/usr/bin/env bash
set -euo pipefail

sdk_root="${ANDROID_HOME:-${ANDROID_SDK_ROOT:-}}"
if [[ -z "$sdk_root" ]]; then
  echo 'ANDROID_HOME or ANDROID_SDK_ROOT must be set by mise before running android:setup.' >&2
  exit 1
fi

sdkmanager "--sdk_root=$sdk_root" \
  'platform-tools' \
  'platforms;android-37' \
  'build-tools;37.0.0'

echo 'Android SDK licenses are interactive; answer them in the prompt.'
sdkmanager "--sdk_root=$sdk_root" '--licenses'
