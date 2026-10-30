package tools

import (
	"archive/zip"
	"bytes"
	"os"
	"path/filepath"
	"testing"
)

func TestExtractZipExtractsRegularFiles(t *testing.T) {
	destination := t.TempDir()
	archive := zipBytes(t, zipEntry{name: "cmdline-tools/bin/sdkmanager", body: "sdk", mode: 0o755})
	if err := ExtractZip(bytes.NewReader(archive), int64(len(archive)), destination); err != nil {
		t.Fatal(err)
	}
	contents, err := os.ReadFile(filepath.Join(destination, "cmdline-tools", "bin", "sdkmanager"))
	if err != nil || string(contents) != "sdk" {
		t.Fatalf("extracted contents = %q, %v", contents, err)
	}
}

func TestExtractZipRejectsUnsafeEntries(t *testing.T) {
	tests := []zipEntry{
		{name: "../outside", body: "bad", mode: 0o644},
		{name: "/absolute", body: "bad", mode: 0o644},
		{name: "link", body: "../outside", mode: os.ModeSymlink | 0o777},
	}
	for _, entry := range tests {
		t.Run(entry.name, func(t *testing.T) {
			destination := t.TempDir()
			archive := zipBytes(t, entry)
			if err := ExtractZip(bytes.NewReader(archive), int64(len(archive)), destination); err == nil {
				t.Fatal("expected unsafe archive to be rejected")
			}
		})
	}
}

type zipEntry struct {
	name string
	body string
	mode os.FileMode
}

func zipBytes(t *testing.T, entries ...zipEntry) []byte {
	t.Helper()
	var buffer bytes.Buffer
	writer := zip.NewWriter(&buffer)
	for _, entry := range entries {
		header := &zip.FileHeader{Name: entry.name, Method: zip.Store}
		header.SetMode(entry.mode)
		file, err := writer.CreateHeader(header)
		if err != nil {
			t.Fatal(err)
		}
		if _, err := file.Write([]byte(entry.body)); err != nil {
			t.Fatal(err)
		}
	}
	if err := writer.Close(); err != nil {
		t.Fatal(err)
	}
	return buffer.Bytes()
}
