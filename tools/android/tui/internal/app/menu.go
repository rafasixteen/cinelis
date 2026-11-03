package app

import (
	"errors"

	"github.com/charmbracelet/huh"
	"github.com/rafasixteen/cinelis/tools/android/tui/internal/tui"
)

type HuhPrompter struct{}

func (HuhPrompter) Select(title string, options []Option) (string, error) {
	huhOptions := make([]huh.Option[string], 0, len(options))
	for _, option := range options {
		label := option.Label
		if option.Description != "" {
			label += " — " + option.Description
		}
		huhOptions = append(huhOptions, huh.NewOption(label, option.Value))
	}
	var value string
	if err := tui.RunSelect(title, huhOptions, &value); err != nil {
		return "", err
	}
	return value, nil
}

func IsPromptCancellation(err error) bool {
	return errors.Is(err, huh.ErrUserAborted)
}

func mainOptions() []Option {
	return []Option{
		{"Watch and reload", "Rebuild the app when Android sources change", string(ActionWatch)},
		{"Build release", "Create release APK and Android App Bundle", string(ActionRelease)},
		{"Open scrcpy", "Mirror and control a selected device", string(ActionScrcpy)},
		{"Open logcat", "Stream logs from a selected device", string(ActionLogcat)},
		{"Check tools", "Inspect Android development dependencies and mise setup guidance", string(ActionTools)},
		{"Exit", "", string(ActionExit)},
	}
}
