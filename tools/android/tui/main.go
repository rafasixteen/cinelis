package main

import (
	"context"
	"fmt"
	"io"
	"os"
	"os/signal"
	"syscall"

	"github.com/rafasixteen/cinelis/tools/android/tui/internal/app"
	"github.com/rafasixteen/cinelis/tools/android/tui/internal/repository"
)

const usage = `CineLis Android developer menu

Usage:
  cinelis-android
  cinelis-android --help

Watch and reload the app, build release artifacts, inspect a device, and install
local Android development dependencies from a terminal menu.
`

func main() {
	os.Exit(run(os.Args[1:], os.Stdout, os.Stderr))
}

func run(args []string, out, errOut io.Writer) int {
	if len(args) == 1 && (args[0] == "--help" || args[0] == "-h") {
		fmt.Fprint(out, usage)
		return 0
	}
	if len(args) != 0 {
		fmt.Fprintf(errOut, "unexpected arguments: %v\n\n%s", args, usage)
		return 2
	}
	root, err := repository.Current()
	if err != nil {
		fmt.Fprintln(errOut, "CineLis repository:", err)
		return 1
	}
	context, stop := signal.NotifyContext(context.Background(), os.Interrupt, syscall.SIGTERM)
	defer stop()
	if err := app.New(root, out, errOut).Run(context); err != nil {
		fmt.Fprintln(errOut, err)
		return 1
	}
	return 0
}
