package tools

import (
	"fmt"
	"os"
	"path/filepath"
	"regexp"
	"strconv"
	"strings"

	"github.com/rafasixteen/cinelis/tools/android/tui/internal/platform"
)

type ProbeRunner interface {
	LookPath(name string) (string, error)
	Output(name string, args ...string) ([]byte, error)
}

type ProbeContext struct {
	OS     platform.OS
	Root   string
	Home   string
	Env    map[string]string
	Runner ProbeRunner
}

func ProbeAll(context ProbeContext) []Status {
	sdkRoot := SDKRoot(context.OS, context.Home, context.Env)
	statuses := make([]Status, 0, len(Catalog()))
	for _, tool := range Catalog() {
		status := Status{Tool: tool}
		switch tool.ID {
		case ToolGo:
			status = probeExecutable(context.Runner, status, "go", "version")
		case ToolJava:
			status = probeJava(context.Runner, status)
		case ToolSDKManager:
			path := findSDKTool(context.Runner, context.OS, sdkRoot)
			status = probeKnownPath(context.Runner, status, path, "--version")
		case ToolADB:
			path := findExecutable(context.Runner, "adb", filepath.Join(sdkRoot, "platform-tools", ExecutableName(context.OS, "adb")))
			status = probeKnownPath(context.Runner, status, path, "version")
		case ToolPlatform37:
			for _, candidate := range []string{
				filepath.Join(sdkRoot, "platforms", "android-37", "android.jar"),
				filepath.Join(sdkRoot, "platforms", "android-37.0", "android.jar"),
			} {
				if regularFile(candidate) {
					status.Path = candidate
					status.Ready = true
					break
				}
			}
		case ToolBuildTools37:
			directory := filepath.Join(sdkRoot, "build-tools", "37.0.0")
			status.Path = directory
			status.Ready = regularFile(filepath.Join(directory, ExecutableName(context.OS, "aapt2"))) &&
				regularFile(filepath.Join(directory, ExecutableName(context.OS, "apksigner")))
		case ToolGradleWrapper:
			wrapper := "gradlew"
			if context.OS == platform.Windows {
				wrapper = "gradlew.bat"
			}
			status.Path = filepath.Join(context.Root, "android", wrapper)
			status.Ready = regularFile(status.Path)
		case ToolScrcpy:
			status = probeExecutable(context.Runner, status, "scrcpy", "--version")
		}
		if !status.Ready && status.Detail == "" {
			status.Detail = "Not installed or not detected."
		}
		statuses = append(statuses, status)
	}
	return statuses
}

func SDKRoot(osName platform.OS, home string, env map[string]string) string {
	if value := env["ANDROID_HOME"]; value != "" {
		return value
	}
	if value := env["ANDROID_SDK_ROOT"]; value != "" {
		return value
	}
	if osName == platform.Windows {
		if value := env["LOCALAPPDATA"]; value != "" {
			return filepath.Join(value, "Android", "Sdk")
		}
		return filepath.Join(home, "AppData", "Local", "Android", "Sdk")
	}
	if value := env["XDG_DATA_HOME"]; value != "" {
		return filepath.Join(value, "android-sdk")
	}
	return filepath.Join(home, ".local", "share", "android-sdk")
}

func ExecutableName(osName platform.OS, name string) string {
	if osName != platform.Windows {
		return name
	}
	if name == "sdkmanager" || name == "apksigner" || name == "android" {
		return name + ".bat"
	}
	return name + ".exe"
}

func probeExecutable(runner ProbeRunner, status Status, executable string, args ...string) Status {
	path, err := runner.LookPath(executable)
	if err != nil {
		return status
	}
	return probeKnownPath(runner, status, path, args...)
}

func probeKnownPath(runner ProbeRunner, status Status, path string, args ...string) Status {
	if path == "" {
		return status
	}
	output, err := runner.Output(path, args...)
	if err != nil {
		status.Detail = fmt.Sprintf("Found at %s but it did not run successfully.", path)
		return status
	}
	status.Ready = true
	status.Path = path
	status.Version = firstLine(string(output))
	return status
}

func probeJava(runner ProbeRunner, status Status) Status {
	javaPath, javaErr := runner.LookPath("java")
	javacPath, javacErr := runner.LookPath("javac")
	if javaErr != nil || javacErr != nil {
		status.Detail = "Java 17 or later and javac are required."
		return status
	}
	output, err := runner.Output(javaPath, "-version")
	if err != nil {
		status.Detail = "Java was found but could not report its version."
		return status
	}
	major := javaMajor(string(output))
	if major < 17 || major > 25 {
		status.Detail = fmt.Sprintf("A JDK from 17 through 25 with javac is required by the Android build; detected Java %d.", major)
		return status
	}
	if _, err := runner.Output(javacPath, "-version"); err != nil {
		status.Detail = "Java 17 or later and a working javac are required."
		return status
	}
	status.Ready = true
	status.Path = javaPath
	status.Version = firstLine(string(output))
	return status
}

var javaVersionPattern = regexp.MustCompile(`version\s+"?(\d+)`)

func javaMajor(value string) int {
	matches := javaVersionPattern.FindStringSubmatch(value)
	if len(matches) != 2 {
		return 0
	}
	major, _ := strconv.Atoi(matches[1])
	return major
}

func findExecutable(runner ProbeRunner, name, fallback string) string {
	if path, err := runner.LookPath(name); err == nil {
		return path
	}
	if regularFile(fallback) {
		return fallback
	}
	return ""
}

func findSDKTool(runner ProbeRunner, osName platform.OS, sdkRoot string) string {
	modern := filepath.Join(sdkRoot, "cmdline-tools", "latest", "bin", ExecutableName(osName, "android"))
	if regularFile(modern) {
		return modern
	}
	if path, err := runner.LookPath("sdkmanager"); err == nil {
		return path
	}
	legacy := filepath.Join(sdkRoot, "cmdline-tools", "latest", "bin", ExecutableName(osName, "sdkmanager"))
	if regularFile(legacy) {
		return legacy
	}
	return ""
}

func regularFile(path string) bool {
	info, err := os.Stat(path)
	return err == nil && info.Mode().IsRegular()
}

func firstLine(value string) string {
	value = strings.TrimSpace(value)
	if index := strings.IndexByte(value, '\n'); index >= 0 {
		return value[:index]
	}
	return value
}
