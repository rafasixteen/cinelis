package tui

import (
	"bufio"
	"errors"
	"fmt"
	"io"
	"os"

	"github.com/charmbracelet/bubbles/key"
	"github.com/charmbracelet/huh"
	"github.com/charmbracelet/lipgloss"
)

const menuUsageHint = "↑/↓ Navigate   Enter Select   Esc Back"

var (
	okStyle      = lipgloss.NewStyle().Foreground(lipgloss.Color("10"))
	errorStyle   = lipgloss.NewStyle().Foreground(lipgloss.Color("9"))
	mutedStyle   = lipgloss.NewStyle().Foreground(lipgloss.Color("8"))
	headingStyle = lipgloss.NewStyle().Bold(true)
)

func StatusOK(value string) string    { return okStyle.Render(value) }
func StatusError(value string) string { return errorStyle.Render(value) }
func StatusMuted(value string) string { return mutedStyle.Render(value) }
func Heading(value string) string     { return headingStyle.Render(value) }

func RunSelect[T comparable](title string, options []huh.Option[T], value *T) error {
	keymap := huh.NewDefaultKeyMap()
	keymap.Quit = key.NewBinding(
		key.WithKeys("esc", "ctrl+c"),
		key.WithHelp("esc", "back"),
	)
	selectField := huh.NewSelect[T]().Options(options...).Value(value)
	usage := huh.NewNote().Title(menuUsageHint)
	group := huh.NewGroup(selectField, usage).
		Title(title).
		WithShowHelp(false).
		WithKeyMap(keymap)
	return huh.NewForm(group).
		WithShowHelp(false).
		WithKeyMap(keymap).
		Run()
}

func Note(out io.Writer, title, message string) {
	fmt.Fprintln(out)
	fmt.Fprintln(out, headingStyle.Render(title))
	fmt.Fprintln(out, message)
}

func Pause(out io.Writer) {
	fmt.Fprintln(out)
	fmt.Fprintln(out, StatusMuted("Press Enter to return."))
	_, _ = bufio.NewReader(os.Stdin).ReadString('\n')
}

func IsCancelled(err error) bool {
	return errors.Is(err, huh.ErrUserAborted)
}
