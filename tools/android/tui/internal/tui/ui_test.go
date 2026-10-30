package tui

import (
	"bytes"
	"errors"
	"strings"
	"testing"

	"github.com/charmbracelet/huh"
)

func TestNoteWritesHeadingAndMessage(t *testing.T) {
	var out bytes.Buffer
	Note(&out, "Android tools", "Everything is ready.")
	got := out.String()
	if !strings.Contains(got, "Android tools") || !strings.Contains(got, "Everything is ready.") {
		t.Fatalf("Note() output = %q", got)
	}
}

func TestIsCancelledOnlyClassifiesUserAbort(t *testing.T) {
	if !IsCancelled(huh.ErrUserAborted) {
		t.Fatal("IsCancelled() rejected huh.ErrUserAborted")
	}
	if IsCancelled(errors.New("failure")) {
		t.Fatal("IsCancelled() accepted an unrelated error")
	}
}

func TestStatusHelpersKeepMessageVisible(t *testing.T) {
	for name, got := range map[string]string{
		"ok": StatusOK("ready"), "error": StatusError("missing"), "muted": StatusMuted("optional"),
	} {
		if !strings.Contains(got, map[string]string{"ok": "ready", "error": "missing", "muted": "optional"}[name]) {
			t.Fatalf("%s status hid its message: %q", name, got)
		}
	}
}
