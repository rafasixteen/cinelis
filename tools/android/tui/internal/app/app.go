package app

import (
	stdcontext "context"
	"errors"
	"fmt"
	"io"
	"os"
	"strings"
	"time"

	"github.com/rafasixteen/cinelis/tools/android/tui/internal/android"
	"github.com/rafasixteen/cinelis/tools/android/tui/internal/platform"
	"github.com/rafasixteen/cinelis/tools/android/tui/internal/tools"
)

type Action string

const (
	ActionWatch   Action = "watch"
	ActionRelease Action = "release"
	ActionScrcpy  Action = "scrcpy"
	ActionLogcat  Action = "logcat"
	ActionTools   Action = "tools"
	ActionExit    Action = "exit"
)

type Option struct {
	Label       string
	Description string
	Value       string
}

type Prompter interface {
	Select(title string, options []Option) (string, error)
	Confirm(title, description string) (bool, error)
}

type CommandRunner interface {
	LookPath(name string) (string, error)
	Output(name string, args ...string) ([]byte, error)
	Run(stdcontext.Context, platform.Command) error
}

type App struct {
	Root          string
	OS            platform.OS
	Runner        CommandRunner
	Prompter      Prompter
	Out           io.Writer
	Err           io.Writer
	Home          string
	Env           map[string]string
	Probe         func() []tools.Status
	JavaHomes     []string
	WatchInterval time.Duration
}

func New(root string, out, errOut io.Writer) *App {
	home, _ := os.UserHomeDir()
	runner := platform.ExecRunner{Stdin: os.Stdin, Stdout: out, Stderr: errOut}
	application := &App{
		Root: root, OS: platform.CurrentOS(), Runner: runner, Prompter: HuhPrompter{},
		Out: out, Err: errOut, Home: home, Env: currentEnvironment(), WatchInterval: time.Second,
	}
	application.Probe = application.probeTools
	return application
}

func (app *App) Run(context stdcontext.Context) error {
	for {
		selection, err := app.Prompter.Select("CineLis Android", mainOptions())
		if err != nil {
			if IsPromptCancellation(err) {
				return nil
			}
			return err
		}
		action := Action(selection)
		if action == ActionExit {
			return nil
		}
		if err := app.Execute(context, action); err != nil {
			if context.Err() != nil || errors.Is(err, stdcontext.Canceled) {
				return nil
			}
			fmt.Fprintf(app.Err, "\nError: %v\n", err)
		}
	}
}

func (app *App) Execute(context stdcontext.Context, action Action) error {
	project := android.NewProject(app.Root)
	switch action {
	case ActionWatch:
		return app.watch(context)
	case ActionRelease:
		return app.runGradle(context, android.ReleaseCommand(app.OS, project))
	case ActionScrcpy:
		return app.runScrcpy(context)
	case ActionLogcat:
		return app.runLogcat(context)
	case ActionTools:
		return app.toolsMenu(context)
	default:
		return fmt.Errorf("unknown action %q", action)
	}
}

func (app *App) probeTools() []tools.Status {
	statuses := tools.ProbeAll(tools.ProbeContext{
		OS: app.OS, Root: app.Root, Home: app.Home, Env: app.Env, Runner: app.Runner,
	})
	for index := range statuses {
		if statuses[index].Tool.ID != tools.ToolJava || statuses[index].Ready {
			continue
		}
		if javaHome, err := app.gradleJavaHome(); err == nil {
			statuses[index].Ready = true
			statuses[index].Path = javaHome
			statuses[index].Detail = "A compatible Gradle JDK was found and will be used for Android builds."
		}
	}
	return statuses
}

func currentEnvironment() map[string]string {
	values := make(map[string]string)
	for _, item := range os.Environ() {
		key, value, found := strings.Cut(item, "=")
		if found {
			values[key] = value
		}
	}
	return values
}
