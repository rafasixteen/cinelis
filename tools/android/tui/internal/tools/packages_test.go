package tools

import (
	"reflect"
	"testing"

	"github.com/rafasixteen/cinelis/tools/android/tui/internal/platform"
)

func TestPackageInstallCommands(t *testing.T) {
	tests := []struct {
		name    string
		os      platform.OS
		manager PackageManager
		tool    ToolID
		want    platform.Command
	}{
		{"pacman go", platform.Linux, ManagerPacman, ToolGo, platform.Command{Name: "sudo", Args: []string{"pacman", "-S", "--needed", "go"}}},
		{"apt java", platform.Linux, ManagerAPT, ToolJava, platform.Command{Name: "sudo", Args: []string{"apt-get", "install", "-y", "openjdk-17-jdk"}}},
		{"dnf go", platform.Linux, ManagerDNF, ToolGo, platform.Command{Name: "sudo", Args: []string{"dnf", "install", "-y", "golang"}}},
		{"zypper java", platform.Linux, ManagerZypper, ToolJava, platform.Command{Name: "sudo", Args: []string{"zypper", "install", "-y", "java-17-openjdk-devel"}}},
		{"winget scrcpy", platform.Windows, ManagerWinget, ToolScrcpy, platform.Command{Name: "winget", Args: []string{"install", "--exact", "--id", "Genymobile.scrcpy"}}},
		{"choco go", platform.Windows, ManagerChoco, ToolGo, platform.Command{Name: "choco", Args: []string{"install", "-y", "golang"}}},
		{"scoop java", platform.Windows, ManagerScoop, ToolJava, platform.Command{Name: "scoop", Args: []string{"install", "temurin17-jdk"}}},
	}
	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			got, err := PackageInstallCommand(test.os, test.manager, test.tool, true)
			if err != nil {
				t.Fatal(err)
			}
			if !reflect.DeepEqual(got, test.want) {
				t.Fatalf("command = %#v, want %#v", got, test.want)
			}
		})
	}
}

func TestUnsupportedPackageMappingReturnsManualGuidance(t *testing.T) {
	if _, err := PackageInstallCommand(platform.Linux, ManagerDNF, ToolScrcpy, true); err == nil {
		t.Fatal("DNF scrcpy mapping guessed a command")
	}
	if _, err := PackageInstallCommand(platform.Linux, ManagerAPT, ToolADB, true); err == nil {
		t.Fatal("ADB should be installed through sdkmanager")
	}
}

func TestPackageCommandsDoNotUseSudoWhenAlreadyElevated(t *testing.T) {
	got, err := PackageInstallCommand(platform.Linux, ManagerPacman, ToolGo, false)
	if err != nil {
		t.Fatal(err)
	}
	if got.Name != "pacman" {
		t.Fatalf("command name = %q, want pacman", got.Name)
	}
}
