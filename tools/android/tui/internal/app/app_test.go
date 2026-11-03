package app

import (
	"bytes"
	"context"
	"errors"
	"os"
	"path/filepath"
	"strings"
	"testing"

	"github.com/rafasixteen/cinelis/tools/android/tui/internal/platform"
	"github.com/rafasixteen/cinelis/tools/android/tui/internal/tools"
)

func TestMainBuildActionsIssueExpectedGradleTasks(t *testing.T) {
	tests := []struct {
		action Action
		task   string
	}{
		{ActionRelease, "assembleRelease"},
	}
	for _, test := range tests {
		t.Run(string(test.action), func(t *testing.T) {
			runner := newFakeRunner()
			application := testApp(runner, &fakePrompter{})
			if err := application.Execute(context.Background(), test.action); err != nil {
				t.Fatal(err)
			}
			assertRunContains(t, runner.runs, "gradlew", test.task)
		})
	}
}

func TestToolsMenuDirectsMissingToolsToMiseWithoutRunningCommands(t *testing.T) {
	runner := newFakeRunner()
	prompter := &fakePrompter{selections: []string{string(tools.ToolGo), toolsBack}}
	application := testApp(runner, prompter)
	application.Probe = func() []tools.Status { return statusesForApp(false) }

	if err := application.toolsMenu(context.Background()); err != nil {
		t.Fatal(err)
	}
	if len(runner.runs) != 0 {
		t.Fatalf("diagnostics menu ran installer commands: %#v", runner.runs)
	}
	output := application.Out.(*bytes.Buffer).String()
	if !strings.Contains(output, "mise install") {
		t.Fatalf("missing mise install guidance: %s", output)
	}
}

func TestToolsMenuDirectsMissingSDKToolsToTaskWrapper(t *testing.T) {
	runner := newFakeRunner()
	prompter := &fakePrompter{selections: []string{string(tools.ToolADB), toolsBack}}
	application := testApp(runner, prompter)
	statuses := statusesForApp(true)
	for index := range statuses {
		if statuses[index].Tool.ID == tools.ToolADB {
			statuses[index].Ready = false
		}
	}
	application.Probe = func() []tools.Status { return statuses }

	if err := application.toolsMenu(context.Background()); err != nil {
		t.Fatal(err)
	}
	output := application.Out.(*bytes.Buffer).String()
	if !strings.Contains(output, "task android:setup") {
		t.Fatalf("missing task wrapper guidance: %s", output)
	}
}

func TestRunReturnsToMenuAfterActionFailure(t *testing.T) {
	runner := newFakeRunner()
	runner.runErrors = []error{errors.New("release failed")}
	prompter := &fakePrompter{selections: []string{string(ActionRelease), string(ActionExit)}}
	application := testApp(runner, prompter)
	if err := application.Run(context.Background()); err != nil {
		t.Fatal(err)
	}
	if !bytes.Contains(application.Err.(*bytes.Buffer).Bytes(), []byte("release failed")) {
		t.Fatalf("failure was not reported: %s", application.Err.(*bytes.Buffer).String())
	}
}

func TestLogcatCancellationIsGraceful(t *testing.T) {
	runner := newFakeRunner()
	runner.blockUntilCancelled = true
	application := testApp(runner, &fakePrompter{selections: []string{"device-42"}})
	context, cancel := context.WithCancel(context.Background())
	cancel()
	if err := application.Execute(context, ActionLogcat); err != nil {
		t.Fatalf("cancelled logcat returned %v", err)
	}
}

func testApp(runner *fakeRunner, prompter *fakePrompter) *App {
	javaHome := filepath.Join(os.TempDir(), "cinelis-test-jdk")
	_ = os.MkdirAll(filepath.Join(javaHome, "bin"), 0o755)
	_ = os.WriteFile(filepath.Join(javaHome, "bin", "java"), []byte("java"), 0o755)
	_ = os.WriteFile(filepath.Join(javaHome, "bin", "jlink"), []byte("jlink"), 0o755)
	return &App{
		Root: filepath.Join("repo with spaces"), OS: platform.Linux,
		Runner: runner, Prompter: prompter,
		Out: &bytes.Buffer{}, Err: &bytes.Buffer{},
		Home: "/home/test", Env: map[string]string{},
		JavaHomes: []string{javaHome},
		Probe:     func() []tools.Status { return statusesForApp(true) },
	}
}

func statusesForApp(goReady bool) []tools.Status {
	statuses := make([]tools.Status, 0, len(tools.Catalog()))
	for _, tool := range tools.Catalog() {
		status := tools.Status{Tool: tool, Ready: true, Path: string(tool.ID)}
		if tool.ID == tools.ToolGo {
			status.Ready = goReady
		}
		statuses = append(statuses, status)
	}
	return statuses
}

type fakePrompter struct {
	selections []string
}

func (prompt *fakePrompter) Select(_ string, _ []Option) (string, error) {
	if len(prompt.selections) == 0 {
		return "", errors.New("no selection")
	}
	value := prompt.selections[0]
	prompt.selections = prompt.selections[1:]
	return value, nil
}

type fakeRunner struct {
	runs                []platform.Command
	runErrors           []error
	blockUntilCancelled bool
}

func newFakeRunner() *fakeRunner { return &fakeRunner{} }

func (runner *fakeRunner) LookPath(name string) (string, error) {
	return name, nil
}

func (runner *fakeRunner) Output(name string, args ...string) ([]byte, error) {
	if name == "adb" && len(args) >= 1 && args[0] == "devices" {
		return []byte("List of devices attached\nemulator-5554 device model:Pixel_9\ndevice-42 device model:CineLis_Test\n"), nil
	}
	return []byte("version 17"), nil
}

func (runner *fakeRunner) Run(context context.Context, command platform.Command) error {
	runner.runs = append(runner.runs, command)
	if runner.blockUntilCancelled {
		<-context.Done()
		return context.Err()
	}
	if len(runner.runErrors) > 0 {
		err := runner.runErrors[0]
		runner.runErrors = runner.runErrors[1:]
		return err
	}
	return nil
}

func assertRunContains(t *testing.T, commands []platform.Command, values ...string) {
	t.Helper()
	for _, command := range commands {
		joined := command.Name + " " + strings.Join(command.Args, " ")
		matched := true
		for _, value := range values {
			if !strings.Contains(joined, value) {
				matched = false
			}
		}
		if matched {
			return
		}
	}
	t.Fatalf("no command contains %v: %#v", values, commands)
}
