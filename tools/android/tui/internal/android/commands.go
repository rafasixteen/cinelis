package android

import (
	"path/filepath"

	"github.com/rafasixteen/cinelis/tools/android/tui/internal/platform"
)

const (
	ApplicationID = "com.cinelis.app"
	MainActivity  = "cinelis.MainActivity"
)

type Project struct {
	Root       string
	AndroidDir string
	APKPath    string
}

func NewProject(root string) Project {
	androidDirectory := filepath.Join(root, "android")
	return Project{
		Root:       root,
		AndroidDir: androidDirectory,
		APKPath:    filepath.Join(androidDirectory, "app", "build", "outputs", "apk", "debug", "app-debug.apk"),
	}
}

func GradleCommand(osName platform.OS, project Project, tasks ...string) platform.Command {
	if osName == platform.Windows {
		return platform.Command{Name: "cmd.exe", Args: append([]string{"/C", "gradlew.bat"}, tasks...), Dir: project.AndroidDir}
	}
	return platform.Command{Name: "./gradlew", Args: append([]string(nil), tasks...), Dir: project.AndroidDir}
}

func BuildDebugCommand(osName platform.OS, project Project) platform.Command {
	return GradleCommand(osName, project, "assembleDebug")
}

func ReleaseCommand(osName platform.OS, project Project) platform.Command {
	return GradleCommand(osName, project, "assembleRelease", "bundleRelease")
}

func InstallDebugCommand(adbPath string, project Project, serial string) platform.Command {
	return platform.Command{
		Name: adbPath, Args: []string{"-s", serial, "install", "-r", project.APKPath}, Dir: project.AndroidDir,
	}
}

func LaunchCommand(adbPath string, _ Project, serial string) platform.Command {
	return platform.Command{
		Name: adbPath,
		Args: []string{"-s", serial, "shell", "am", "start", "-n", ApplicationID + "/" + MainActivity},
	}
}

func LogcatCommand(adbPath, serial string) platform.Command {
	return platform.Command{Name: adbPath, Args: []string{"-s", serial, "logcat"}}
}

func ScrcpyCommand(scrcpyPath, serial string) platform.Command {
	return platform.Command{Name: scrcpyPath, Args: []string{"--serial", serial, "--stay-awake"}}
}

func StartADBCommand(adbPath string) platform.Command {
	return platform.Command{Name: adbPath, Args: []string{"start-server"}}
}

func ListDevicesCommand(adbPath string) platform.Command {
	return platform.Command{Name: adbPath, Args: []string{"devices", "-l"}}
}
