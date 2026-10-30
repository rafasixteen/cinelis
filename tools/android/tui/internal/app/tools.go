package app

import (
	"context"
	"fmt"
	"strings"

	"github.com/rafasixteen/cinelis/tools/android/tui/internal/platform"
	"github.com/rafasixteen/cinelis/tools/android/tui/internal/tools"
	"github.com/rafasixteen/cinelis/tools/android/tui/internal/tui"
)

const (
	toolsInstallAll = "install-all"
	toolsBack       = "back"
)

func (app *App) toolsMenu(context context.Context) error {
	for {
		statuses := app.Probe()
		options := []Option{{Label: "Install all missing tools", Description: "Install every supported missing dependency in order", Value: toolsInstallAll}}
		for _, status := range statuses {
			mark := tui.StatusError("✗")
			if status.Ready {
				mark = tui.StatusOK("✓")
			}
			detail := status.Tool.Description
			if status.Ready && status.Version != "" {
				detail += " " + status.Version
			} else if !status.Ready && status.Detail != "" {
				detail += " " + status.Detail
			}
			options = append(options, Option{Label: mark + " " + status.Tool.Name, Description: detail, Value: string(status.Tool.ID)})
		}
		options = append(options, Option{Label: "Back", Value: toolsBack})
		selection, err := app.Prompter.Select("Android tool status", options)
		if err != nil {
			if IsPromptCancellation(err) {
				return nil
			}
			return err
		}
		if selection == toolsBack {
			return nil
		}
		if selection == toolsInstallAll {
			if err := app.installAll(context, statuses); err != nil {
				fmt.Fprintf(app.Err, "install tools: %v\n", err)
			}
			continue
		}
		status, found := findStatus(statuses, tools.ToolID(selection))
		if !found {
			return fmt.Errorf("unknown tool %q", selection)
		}
		if status.Ready {
			fmt.Fprintln(app.Out, status.Tool.Name+" is already installed.")
			continue
		}
		if err := app.installTool(context, status); err != nil {
			fmt.Fprintf(app.Err, "install %s: %v\n", status.Tool.Name, err)
		}
	}
}

func (app *App) installAll(context context.Context, statuses []tools.Status) error {
	plan := tools.BuildInstallPlan(statuses)
	if len(plan) == 0 {
		fmt.Fprintln(app.Out, "All Android development tools are installed.")
		return nil
	}
	confirmed, err := app.Prompter.Confirm("Install all missing tools?", "Package-manager commands and Android SDK installers will be shown as they run. License acceptance remains interactive.")
	if err != nil || !confirmed {
		return err
	}
	for _, step := range plan {
		status, found := findStatus(statuses, step.ToolID)
		if !found {
			continue
		}
		if err := app.installToolConfirmed(context, status); err != nil {
			return fmt.Errorf("%s: %w", status.Tool.Name, err)
		}
	}
	app.Probe()
	return nil
}

func (app *App) installTool(context context.Context, status tools.Status) error {
	if status.Ready {
		return nil
	}
	if !status.Tool.Installable {
		return fmt.Errorf("%s belongs to the repository and cannot be installed by this menu", status.Tool.Name)
	}
	confirmed, err := app.Prompter.Confirm("Install "+status.Tool.Name+"?", status.Tool.Description+" The command may prompt for administrator credentials or license acceptance.")
	if err != nil || !confirmed {
		return err
	}
	if err := app.installToolConfirmed(context, status); err != nil {
		return err
	}
	app.Probe()
	return nil
}

func (app *App) installToolConfirmed(context context.Context, status tools.Status) error {
	switch status.Tool.ID {
	case tools.ToolGo, tools.ToolJava, tools.ToolScrcpy:
		manager, found := tools.DetectPackageManager(app.OS, app.Runner.LookPath)
		if !found {
			return fmt.Errorf("no supported package manager found")
		}
		command, err := tools.PackageInstallCommand(app.OS, manager, status.Tool.ID, app.OS == platform.Linux)
		if err != nil {
			return err
		}
		fmt.Fprintln(app.Out, "$ "+command.Name+" "+strings.Join(command.Args, " "))
		return app.Runner.Run(context, command)
	case tools.ToolSDKManager:
		installer := tools.SDKInstaller{
			OS: app.OS, SDKRoot: app.sdkRoot(), Downloader: tools.HTTPDownloader{},
		}
		backup, err := installer.InstallCommandLineTools(context)
		if err == nil && backup != "" {
			fmt.Fprintln(app.Out, "Previous command-line tools backed up at "+backup)
		}
		return err
	case tools.ToolADB, tools.ToolPlatform37, tools.ToolBuildTools37:
		licenses := tools.SDKLicensesCommand(app.OS, app.sdkRoot())
		if licenses.Name != "" {
			fmt.Fprintln(app.Out, "Android licenses are interactive; no answers will be accepted automatically.")
			if err := app.Runner.Run(context, licenses); err != nil {
				return err
			}
		}
		return app.Runner.Run(context, tools.SDKPackageCommand(app.OS, app.sdkRoot(), status.Tool.ID))
	case tools.ToolGradleWrapper:
		return fmt.Errorf("restore android/gradlew and android/gradlew.bat from the repository")
	default:
		return fmt.Errorf("automatic installation is not available for %s", status.Tool.Name)
	}
}

func (app *App) sdkRoot() string {
	return tools.SDKRoot(app.OS, app.Home, app.Env)
}

func findStatus(statuses []tools.Status, id tools.ToolID) (tools.Status, bool) {
	for _, status := range statuses {
		if status.Tool.ID == id {
			return status, true
		}
	}
	return tools.Status{}, false
}

func toolsExecutable(osName platform.OS, name string) string {
	return tools.ExecutableName(osName, name)
}
