package app

import (
	"context"
	"fmt"

	"github.com/rafasixteen/cinelis/tools/android/tui/internal/platform"
	"github.com/rafasixteen/cinelis/tools/android/tui/internal/tools"
	"github.com/rafasixteen/cinelis/tools/android/tui/internal/tui"
)

const (
	toolsBack = "back"
)

func (app *App) toolsMenu(_ context.Context) error {
	for {
		statuses := app.Probe()
		options := make([]Option, 0, len(statuses)+1)
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
		status, found := findStatus(statuses, tools.ToolID(selection))
		if !found {
			return fmt.Errorf("unknown tool %q", selection)
		}
		if status.Ready {
			fmt.Fprintln(app.Out, status.Tool.Name+" is already installed.")
			continue
		}
		app.printToolGuidance(status)
	}
}

func (app *App) printToolGuidance(status tools.Status) {
	command := "mise install"
	if status.Tool.ID == tools.ToolADB || status.Tool.ID == tools.ToolPlatform37 || status.Tool.ID == tools.ToolBuildTools37 {
		command = "task android:setup"
	}
	if status.Tool.ID == tools.ToolGradleWrapper {
		fmt.Fprintln(app.Out, "Restore android/gradlew and android/gradlew.bat from the repository.")
		return
	}
	fmt.Fprintf(app.Out, "%s is missing. Run `%s` from the repository root.\n", status.Tool.Name, command)
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
