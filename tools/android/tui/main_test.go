package main

import (
	"bytes"
	"testing"
)

func TestRunSupportsHelp(t *testing.T) {
	var output bytes.Buffer
	if code := run([]string{"--help"}, &output, &output); code != 0 {
		t.Fatalf("exit code = %d", code)
	}
	if !bytes.Contains(output.Bytes(), []byte("CineLis Android developer menu")) {
		t.Fatalf("help output = %q", output.String())
	}
}

func TestRunRejectsUnexpectedArguments(t *testing.T) {
	var output bytes.Buffer
	if code := run([]string{"unexpected"}, &output, &output); code == 0 {
		t.Fatal("expected non-zero exit code")
	}
}
