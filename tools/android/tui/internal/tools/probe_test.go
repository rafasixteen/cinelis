package tools

import (
	"errors"
	"os"
	"path/filepath"
	"strings"
	"testing"

	"github.com/rafasixteen/cinelis/tools/android/tui/internal/platform"
)

type fakeProbeRunner struct {
	paths   map[string]string
	outputs map[string]string
}

func (f fakeProbeRunner) LookPath(name string) (string, error) {
	if path := f.paths[name]; path != "" {
		return path, nil
	}
	return "", errors.New("not found")
}

func (f fakeProbeRunner) Output(name string, args ...string) ([]byte, error) {
	key := name + " " + strings.Join(args, " ")
	if value, ok := f.outputs[key]; ok {
		return []byte(value), nil
	}
	return nil, errors.New("failed")
}

func TestProbeAllRecognizesReadyToolsAndSDKPathsContainingSpaces(t *testing.T) {
	root := t.TempDir()
	sdk := filepath.Join(root, "Android SDK")
	touchProbe(t, filepath.Join(sdk, "cmdline-tools", "latest", "bin", "sdkmanager"))
	touchProbe(t, filepath.Join(sdk, "platform-tools", "adb"))
	touchProbe(t, filepath.Join(sdk, "platforms", "android-37", "android.jar"))
	touchProbe(t, filepath.Join(sdk, "build-tools", "37.0.0", "aapt2"))
	touchProbe(t, filepath.Join(sdk, "build-tools", "37.0.0", "apksigner"))
	touchProbe(t, filepath.Join(root, "android", "gradlew"))
	runner := fakeProbeRunner{
		paths: map[string]string{"go": "/bin/go", "java": "/bin/java", "javac": "/bin/javac", "scrcpy": "/bin/scrcpy"},
		outputs: map[string]string{
			"/bin/go version":       "go version go1.27.3 linux/amd64",
			"/bin/java -version":    "openjdk version \"17.0.12\"",
			"/bin/javac -version":   "javac 17.0.12",
			"/bin/scrcpy --version": "scrcpy 4.1",
			filepath.Join(sdk, "cmdline-tools", "latest", "bin", "sdkmanager") + " --version": "20.0",
			filepath.Join(sdk, "platform-tools", "adb") + " version":                          "Android Debug Bridge version 1.0.41",
		},
	}
	statuses := ProbeAll(ProbeContext{OS: platform.Linux, Root: root, Home: root, Env: map[string]string{"ANDROID_HOME": sdk}, Runner: runner})
	for _, status := range statuses {
		if !status.Ready {
			t.Fatalf("%s not ready: %s", status.Tool.ID, status.Detail)
		}
	}
}

func TestProbeAllRejectsOldJavaAndMissingJavac(t *testing.T) {
	runner := fakeProbeRunner{
		paths:   map[string]string{"java": "/bin/java"},
		outputs: map[string]string{"/bin/java -version": "openjdk version \"11.0.2\""},
	}
	statuses := ProbeAll(ProbeContext{OS: platform.Linux, Root: t.TempDir(), Home: t.TempDir(), Env: map[string]string{}, Runner: runner})
	java := statusByID(t, statuses, ToolJava)
	if java.Ready || !strings.Contains(java.Detail, "17") || !strings.Contains(java.Detail, "javac") {
		t.Fatalf("Java status = %#v", java)
	}
}

func TestProbeAllRejectsJavaTooNewForAndroidBuild(t *testing.T) {
	runner := fakeProbeRunner{
		paths: map[string]string{"java": "/bin/java", "javac": "/bin/javac"},
		outputs: map[string]string{
			"/bin/java -version":  "openjdk version \"27\"",
			"/bin/javac -version": "javac 27",
		},
	}
	statuses := ProbeAll(ProbeContext{OS: platform.Linux, Root: t.TempDir(), Home: t.TempDir(), Env: map[string]string{}, Runner: runner})
	java := statusByID(t, statuses, ToolJava)
	if java.Ready || !strings.Contains(java.Detail, "17") || !strings.Contains(java.Detail, "25") {
		t.Fatalf("Java status = %#v", java)
	}
}

func TestSDKRootPrecedenceAndWindowsExecutableSuffixes(t *testing.T) {
	home := filepath.Join("C:", "Users", "Rafa")
	got := SDKRoot(platform.Windows, home, map[string]string{
		"ANDROID_HOME":     filepath.Join("D:", "Preferred SDK"),
		"ANDROID_SDK_ROOT": filepath.Join("E:", "Other SDK"),
	})
	if got != filepath.Join("D:", "Preferred SDK") {
		t.Fatalf("SDKRoot() = %q", got)
	}
	if got := ExecutableName(platform.Windows, "adb"); got != "adb.exe" {
		t.Fatalf("ExecutableName(adb) = %q", got)
	}
	if got := ExecutableName(platform.Windows, "sdkmanager"); got != "sdkmanager.bat" {
		t.Fatalf("ExecutableName(sdkmanager) = %q", got)
	}
}

func TestProbeAllRecognizesModernAndroidCLIAndPlatform37PointZero(t *testing.T) {
	root := t.TempDir()
	sdk := filepath.Join(root, "sdk")
	touchProbe(t, filepath.Join(sdk, "cmdline-tools", "latest", "bin", "android"))
	touchProbe(t, filepath.Join(sdk, "platforms", "android-37.0", "android.jar"))
	runner := fakeProbeRunner{outputs: map[string]string{
		filepath.Join(sdk, "cmdline-tools", "latest", "bin", "android") + " --version": "android 1.0",
	}}
	statuses := ProbeAll(ProbeContext{OS: platform.Linux, Root: root, Home: root, Env: map[string]string{"ANDROID_HOME": sdk}, Runner: runner})
	sdkManager := statusByID(t, statuses, ToolSDKManager)
	if !sdkManager.Ready || sdkManager.Path != filepath.Join(sdk, "cmdline-tools", "latest", "bin", "android") {
		t.Fatalf("modern CLI status = %#v", sdkManager)
	}
	platformStatus := statusByID(t, statuses, ToolPlatform37)
	if !platformStatus.Ready || !strings.HasSuffix(platformStatus.Path, filepath.Join("platforms", "android-37.0", "android.jar")) {
		t.Fatalf("modern platform status = %#v", platformStatus)
	}
}

func touchProbe(t *testing.T, path string) {
	t.Helper()
	if err := os.MkdirAll(filepath.Dir(path), 0o755); err != nil {
		t.Fatal(err)
	}
	if err := os.WriteFile(path, []byte("tool"), 0o755); err != nil {
		t.Fatal(err)
	}
}

func statusByID(t *testing.T, statuses []Status, id ToolID) Status {
	t.Helper()
	for _, status := range statuses {
		if status.Tool.ID == id {
			return status
		}
	}
	t.Fatalf("missing status %s", id)
	return Status{}
}
