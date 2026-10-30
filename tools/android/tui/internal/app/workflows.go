package app

import (
	stdcontext "context"
	"errors"
	"fmt"
	"os"
	"path/filepath"
	"reflect"
	"regexp"
	"strconv"
	"strings"
	"time"

	"github.com/rafasixteen/cinelis/tools/android/tui/internal/android"
	"github.com/rafasixteen/cinelis/tools/android/tui/internal/platform"
)

func (app *App) selectDevice(context stdcontext.Context) (string, error) {
	adbPath, err := app.executablePath("adb")
	if err != nil {
		return "", err
	}
	if err := app.Runner.Run(context, android.StartADBCommand(adbPath)); err != nil {
		return "", fmt.Errorf("start ADB: %w", err)
	}
	output, err := app.Runner.Output(adbPath, "devices", "-l")
	if err != nil {
		return "", fmt.Errorf("list Android devices: %w", err)
	}
	devices := android.ParseADBDevices(output)
	options := make([]Option, 0, len(devices))
	diagnostics := make([]string, 0)
	for _, device := range devices {
		if device.Selectable {
			options = append(options, Option{Label: device.Label, Value: device.Serial})
		} else {
			diagnostics = append(diagnostics, device.Label+": "+device.Diagnostic)
		}
	}
	if len(options) == 0 {
		message := "No authorized Android devices are connected."
		if len(diagnostics) > 0 {
			message += " " + strings.Join(diagnostics, " ")
		}
		return "", errors.New(message)
	}
	serial, err := app.Prompter.Select("Select Android device", options)
	if err != nil {
		return "", err
	}
	return serial, nil
}

func (app *App) buildInstallLaunch(context stdcontext.Context, serial string) error {
	project := android.NewProject(app.Root)
	adbPath, err := app.executablePath("adb")
	if err != nil {
		return err
	}
	commands := []platform.Command{
		android.BuildDebugCommand(app.OS, project),
		android.InstallDebugCommand(adbPath, project, serial),
		android.LaunchCommand(adbPath, project, serial),
	}
	for index, command := range commands {
		var err error
		if index == 0 {
			err = app.runGradle(context, command)
		} else {
			err = app.Runner.Run(context, command)
		}
		if err != nil {
			return err
		}
	}
	return nil
}

func (app *App) runGradle(context stdcontext.Context, command platform.Command) error {
	javaHome, err := app.gradleJavaHome()
	if err != nil {
		return err
	}
	if command.Env == nil {
		command.Env = make(map[string]string)
	}
	command.Env["JAVA_HOME"] = javaHome
	return app.Runner.Run(context, command)
}

func (app *App) gradleJavaHome() (string, error) {
	candidates := append([]string(nil), app.JavaHomes...)
	if configured := app.Env["JAVA_HOME"]; configured != "" {
		candidates = append([]string{configured}, candidates...)
	} else if java, err := app.Runner.LookPath("java"); err == nil {
		candidates = append(candidates, filepath.Dir(filepath.Dir(java)))
	}
	if len(app.JavaHomes) == 0 {
		candidates = append(candidates, app.discoveredJavaHomes()...)
	}
	seen := make(map[string]bool)
	for _, candidate := range candidates {
		candidate = filepath.Clean(candidate)
		if candidate == "." || seen[candidate] {
			continue
		}
		seen[candidate] = true
		java := filepath.Join(candidate, "bin", toolsExecutable(app.OS, "java"))
		jlink := filepath.Join(candidate, "bin", toolsExecutable(app.OS, "jlink"))
		if _, err := os.Stat(java); err != nil {
			continue
		}
		if _, err := os.Stat(jlink); err != nil {
			continue
		}
		output, err := app.Runner.Output(java, "-version")
		if err != nil {
			continue
		}
		major := javaMajor(string(output))
		if major >= 17 && major <= 25 {
			return candidate, nil
		}
	}
	return "", errors.New("Gradle requires a JDK from 17 through 25 with jlink; install Java 17 and retry")
}

func (app *App) discoveredJavaHomes() []string {
	patterns := []string{
		"/usr/lib/jvm/*",
		filepath.Join(app.Home, ".gradle", "jdks", "*"),
		filepath.Join(app.Home, ".local", "share", "android-studio", "jbr"),
		filepath.Join(app.Home, ".android-studio", "jbr"),
		filepath.Join(`C:\Program Files`, "Java", "*"),
		filepath.Join(`C:\Program Files`, "Eclipse Adoptium", "*"),
	}
	var homes []string
	for _, pattern := range patterns {
		matches, _ := filepath.Glob(pattern)
		homes = append(homes, matches...)
	}
	return homes
}

var javaMajorPattern = regexp.MustCompile(`(?:version|javac)\s+"?(\d+)`)

func javaMajor(value string) int {
	match := javaMajorPattern.FindStringSubmatch(value)
	if len(match) != 2 {
		return 0
	}
	major, _ := strconv.Atoi(match[1])
	return major
}

func (app *App) watch(context stdcontext.Context) error {
	serial, err := app.selectDevice(context)
	if err != nil {
		return err
	}
	if err := app.buildInstallLaunch(context, serial); err != nil {
		return err
	}
	previous, err := android.SnapshotSources(app.Root)
	if err != nil {
		return err
	}
	interval := app.WatchInterval
	if interval <= 0 {
		interval = time.Second
	}
	ticker := time.NewTicker(interval)
	defer ticker.Stop()
	fmt.Fprintln(app.Out, "Watching Android sources. Press Ctrl+C to stop.")
	for {
		select {
		case <-context.Done():
			return nil
		case <-ticker.C:
			current, err := android.SnapshotSources(app.Root)
			if err != nil {
				fmt.Fprintf(app.Err, "watch: %v\n", err)
				continue
			}
			if reflect.DeepEqual(previous, current) {
				continue
			}
			previous = current
			if err := app.buildInstallLaunch(context, serial); err != nil {
				fmt.Fprintf(app.Err, "reload: %v\n", err)
			}
		}
	}
}

func (app *App) runLogcat(context stdcontext.Context) error {
	serial, err := app.selectDevice(context)
	if err != nil {
		if context.Err() != nil || errors.Is(err, stdcontext.Canceled) {
			return nil
		}
		return err
	}
	adbPath, err := app.executablePath("adb")
	if err != nil {
		return err
	}
	err = app.Runner.Run(context, android.LogcatCommand(adbPath, serial))
	if errors.Is(err, stdcontext.Canceled) || context.Err() != nil {
		return nil
	}
	return err
}

func (app *App) runScrcpy(context stdcontext.Context) error {
	serial, err := app.selectDevice(context)
	if err != nil {
		return err
	}
	if app.OS == platform.Linux && android.ScrcpyRunning(app.OS, serial, procInspector{}) {
		fmt.Fprintln(app.Out, "scrcpy is already running for "+serial+".")
		return nil
	}
	scrcpyPath, err := app.executablePath("scrcpy")
	if err != nil {
		return err
	}
	return app.Runner.Run(context, android.ScrcpyCommand(scrcpyPath, serial))
}

func (app *App) executablePath(name string) (string, error) {
	if path, err := app.Runner.LookPath(name); err == nil {
		return path, nil
	}
	if name == "adb" {
		candidate := filepath.Join(app.sdkRoot(), "platform-tools", toolsExecutable(app.OS, name))
		if _, err := os.Stat(candidate); err == nil {
			return candidate, nil
		}
	}
	return "", fmt.Errorf("%s is not installed; open Install tools", name)
}

type procInspector struct{}

func (procInspector) ProcessIDs(name string) ([]int, error) {
	entries, err := os.ReadDir("/proc")
	if err != nil {
		return nil, err
	}
	ids := make([]int, 0)
	for _, entry := range entries {
		pid, err := strconv.Atoi(entry.Name())
		if err != nil {
			continue
		}
		contents, err := os.ReadFile(filepath.Join("/proc", entry.Name(), "comm"))
		if err == nil && strings.TrimSpace(string(contents)) == name {
			ids = append(ids, pid)
		}
	}
	return ids, nil
}

func (procInspector) Args(pid int) ([]string, error) {
	contents, err := os.ReadFile(filepath.Join("/proc", strconv.Itoa(pid), "cmdline"))
	if err != nil {
		return nil, err
	}
	trimmed := strings.TrimRight(string(contents), "\x00")
	if trimmed == "" {
		return nil, nil
	}
	return strings.Split(trimmed, "\x00"), nil
}
