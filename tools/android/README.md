# CineLis Android developer TUI

The Android developer TUI provides one terminal menu for the native app's
common workflows:

- watch Android sources and reload the selected device;
- build release APK/AAB artifacts;
- open `scrcpy` or stream `logcat`;
- inspect and install local Android development tools.

The TUI is written in Go and supports Linux and Windows. It always passes the
selected ADB serial to device-specific commands, so multiple connected devices
remain unambiguous.

## Run

From the repository root:

```bash
cd tools/android/tui
go run .
```

On Windows PowerShell:

```powershell
Set-Location tools/android/tui
go run .
```

The executable discovers the repository root from its current directory, so it
can also be run from a subdirectory after building it.

## Build

Linux:

```bash
cd tools/android/tui
go build -o cinelis-android .
./cinelis-android
```

Windows PowerShell:

```powershell
Set-Location tools/android/tui
go build -o cinelis-android.exe .
.\cinelis-android.exe
```

## Install tools menu

The menu shows a green check or red cross, a short explanation, and detected
version information for:

- Go;
- Java 17–25 JDK and `javac`;
- Android SDK command-line tools and `sdkmanager`;
- ADB / platform-tools;
- Android platform 37;
- Android build-tools 37.0.0;
- the repository Gradle wrapper;
- `scrcpy`.

Select a missing item to install only that dependency, or select **Install all
missing tools** to process supported dependencies in order.

On Linux, package installation supports `pacman`, `apt-get`, `dnf`, and
`zypper`. On Windows it supports `winget`, Chocolatey, and Scoop. Unsupported
package/tool combinations stop with manual guidance instead of guessing a
package name.

Android command-line tools are downloaded from Google's pinned archive,
bounded by a size limit, verified with SHA-256, extracted through path-safe ZIP
handling, and staged before replacing an existing installation. Existing
command-line tools are kept in a timestamped backup.

The TUI prefers the current Android CLI (`android sdk install`) when it is
available and falls back to legacy `sdkmanager` commands for older SDK tool
installations. Gradle builds use an installed JDK from 17 through 25 with
`jlink`; this keeps the Android Gradle Plugin compatible without changing the
shell's `JAVA_HOME` or `PATH`.

The installer deliberately:

- shows package-manager commands before running them;
- asks for confirmation before installation;
- leaves Android SDK license acceptance interactive;
- does not edit shell profiles or `PATH`;
- does not accept licenses automatically;
- does not silently elevate privileges.

If a newly installed executable is not visible to the running process, restart
the terminal after completing the installer.

## Tests

```bash
cd tools/android/tui
go test ./...
go vet ./...
```

Check the Windows build from Linux:

```bash
GOOS=windows GOARCH=amd64 go build .
```
