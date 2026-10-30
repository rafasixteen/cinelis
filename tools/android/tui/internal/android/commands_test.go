package android

import (
	"path/filepath"
	"reflect"
	"testing"

	"github.com/rafasixteen/cinelis/tools/android/tui/internal/platform"
)

func TestGradleCommandsAreOSSpecificAndKeepPathsWithSpacesIntact(t *testing.T) {
	root := filepath.Join(string(filepath.Separator), "work", "Cine Lis")
	project := NewProject(root)
	tests := []struct {
		name string
		os   platform.OS
		got  platform.Command
		want platform.Command
	}{
		{
			name: "linux release",
			os:   platform.Linux,
			got:  ReleaseCommand(platform.Linux, project),
			want: platform.Command{Name: "./gradlew", Args: []string{"assembleRelease", "bundleRelease"}, Dir: filepath.Join(root, "android")},
		},
	}
	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			if !reflect.DeepEqual(test.got, test.want) {
				t.Fatalf("command = %#v, want %#v", test.got, test.want)
			}
		})
	}
}

func TestDeviceCommandsAlwaysTargetSelectedSerial(t *testing.T) {
	project := NewProject(filepath.Join(string(filepath.Separator), "work", "Cine Lis"))
	serial := "device serial"
	tests := []struct {
		name string
		got  platform.Command
		want platform.Command
	}{
		{"install", InstallDebugCommand("C:\\Android SDK\\adb.exe", project, serial), platform.Command{
			Name: "C:\\Android SDK\\adb.exe",
			Args: []string{"-s", serial, "install", "-r", filepath.Join(project.Root, "android", "app", "build", "outputs", "apk", "debug", "app-debug.apk")},
			Dir:  filepath.Join(project.Root, "android"),
		}},
		{"launch", LaunchCommand("adb", project, serial), platform.Command{
			Name: "adb", Args: []string{"-s", serial, "shell", "am", "start", "-n", "com.cinelis.app/cinelis.MainActivity"},
		}},
		{"logcat", LogcatCommand("adb", serial), platform.Command{
			Name: "adb", Args: []string{"-s", serial, "logcat"},
		}},
	}
	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			if !reflect.DeepEqual(test.got, test.want) {
				t.Fatalf("command = %#v, want %#v", test.got, test.want)
			}
		})
	}
}
