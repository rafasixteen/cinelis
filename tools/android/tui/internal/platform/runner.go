package platform

import (
	"context"
	"io"
	"os"
	"os/exec"
)

type Runner interface {
	LookPath(name string) (string, error)
	Output(name string, args ...string) ([]byte, error)
	Run(context.Context, Command) error
}

type ExecRunner struct {
	Stdin  io.Reader
	Stdout io.Writer
	Stderr io.Writer
}

func (runner ExecRunner) LookPath(name string) (string, error) {
	return exec.LookPath(name)
}

func (runner ExecRunner) Output(name string, args ...string) ([]byte, error) {
	return exec.Command(name, args...).CombinedOutput()
}

func (runner ExecRunner) Run(context context.Context, command Command) error {
	process := exec.CommandContext(context, command.Name, command.Args...)
	process.Dir = command.Dir
	process.Env = append(os.Environ(), environment(command.Env)...)
	process.Stdin = runner.Stdin
	process.Stdout = runner.Stdout
	process.Stderr = runner.Stderr
	return process.Run()
}

func environment(values map[string]string) []string {
	result := make([]string, 0, len(values))
	for key, value := range values {
		result = append(result, key+"="+value)
	}
	return result
}
