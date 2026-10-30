package tools

import (
	"fmt"

	"github.com/rafasixteen/cinelis/tools/android/tui/internal/platform"
)

type PackageManager string

const (
	ManagerPacman PackageManager = "pacman"
	ManagerAPT    PackageManager = "apt-get"
	ManagerDNF    PackageManager = "dnf"
	ManagerZypper PackageManager = "zypper"
	ManagerWinget PackageManager = "winget"
	ManagerChoco  PackageManager = "choco"
	ManagerScoop  PackageManager = "scoop"
)

func DetectPackageManager(osName platform.OS, lookPath func(string) (string, error)) (PackageManager, bool) {
	managers := []PackageManager{ManagerPacman, ManagerAPT, ManagerDNF, ManagerZypper}
	if osName == platform.Windows {
		managers = []PackageManager{ManagerWinget, ManagerChoco, ManagerScoop}
	}
	for _, manager := range managers {
		if _, err := lookPath(string(manager)); err == nil {
			return manager, true
		}
	}
	return "", false
}

func PackageInstallCommand(osName platform.OS, manager PackageManager, toolID ToolID, useSudo bool) (platform.Command, error) {
	packageName, ok := packageFor(manager, toolID)
	if !ok {
		return platform.Command{}, fmt.Errorf("%s cannot install %s automatically; use the tool's official installation guidance", manager, toolID)
	}
	var args []string
	switch manager {
	case ManagerPacman:
		args = []string{"-S", "--needed", packageName}
	case ManagerAPT, ManagerDNF, ManagerZypper:
		args = []string{"install", "-y", packageName}
	case ManagerWinget:
		args = []string{"install", "--exact", "--id", packageName}
	case ManagerChoco:
		args = []string{"install", "-y", packageName}
	case ManagerScoop:
		args = []string{"install", packageName}
	default:
		return platform.Command{}, fmt.Errorf("unsupported package manager %q", manager)
	}
	command := platform.Command{Name: string(manager), Args: args}
	if osName == platform.Linux && useSudo {
		command.Args = append([]string{command.Name}, command.Args...)
		command.Name = "sudo"
	}
	return command, nil
}

func packageFor(manager PackageManager, toolID ToolID) (string, bool) {
	packages := map[PackageManager]map[ToolID]string{
		ManagerPacman: {ToolGo: "go", ToolJava: "jdk17-openjdk", ToolScrcpy: "scrcpy"},
		ManagerAPT:    {ToolGo: "golang-go", ToolJava: "openjdk-17-jdk", ToolScrcpy: "scrcpy"},
		ManagerDNF:    {ToolGo: "golang", ToolJava: "java-17-openjdk-devel"},
		ManagerZypper: {ToolGo: "go", ToolJava: "java-17-openjdk-devel"},
		ManagerWinget: {ToolGo: "GoLang.Go", ToolJava: "EclipseAdoptium.Temurin.17.JDK", ToolScrcpy: "Genymobile.scrcpy"},
		ManagerChoco:  {ToolGo: "golang", ToolJava: "temurin17", ToolScrcpy: "scrcpy"},
		ManagerScoop:  {ToolGo: "go", ToolJava: "temurin17-jdk", ToolScrcpy: "scrcpy"},
	}
	byTool, exists := packages[manager]
	if !exists {
		return "", false
	}
	value, exists := byTool[toolID]
	return value, exists
}
