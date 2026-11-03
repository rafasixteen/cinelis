# CineLis Android developer TUI

The Android developer TUI provides one terminal menu for the native app's
common workflows:

- watch Android sources and reload the selected device;
- build release APK/AAB artifacts;
- open `scrcpy` or stream `logcat`;
- inspect local Android development tools and show mise setup guidance.

The TUI is written in Go and supports Linux and Windows. It always passes the
selected ADB serial to device-specific commands, so multiple connected devices
remain unambiguous.

## Setup

From the repository root, install the declared mise tools and Android SDK
packages:

```bash
mise install
task android:setup
```

The setup task installs platform-tools, Android platform 37, and
build-tools 37.0.0. Android SDK license acceptance remains interactive.

The project declares Go, Java 17, Android command-line tools, and scrcpy in
`mise.toml`. The Gradle wrapper remains owned by the repository.

## Run

From the repository root:

```bash
mise exec -- go run ./tools/android/tui
```

On Windows PowerShell:

```powershell
mise exec -- go run .\tools\android\tui
```

Alternatively, activate mise in the shell and run `go run .` from
`tools/android/tui`. The executable discovers the repository root from its
current directory, so it can also be run from a subdirectory after building
it.

## Build

Linux:

```bash
cd tools/android/tui
mise exec -- go build -o cinelis-android .
./cinelis-android
```

Windows PowerShell:

```powershell
Set-Location tools/android/tui
mise exec -- go build -o cinelis-android.exe .
.\cinelis-android.exe
```

## Check tools menu

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

The menu shows a green check or red cross, a short explanation, and detected
version information for the declared tools and Android SDK packages. Selecting
a missing item prints the relevant mise command; the TUI never installs tools
or changes the SDK itself.

Run `mise install` for Go, Java, Android command-line tools, and scrcpy. Run
`task android:setup` for platform-tools, Android platform 37, and
build-tools 37.0.0; the Taskfile delegates to mise. If the TUI is launched directly instead of through
`mise exec` or an activated mise shell, mise-managed executables may not be on
`PATH`.

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
