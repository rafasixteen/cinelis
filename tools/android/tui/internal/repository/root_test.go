package repository

import (
	"os"
	"path/filepath"
	"testing"
)

func TestRootFindsRepositoryFromNestedPathWithSpaces(t *testing.T) {
	root := filepath.Join(t.TempDir(), "Cine Lis")
	mustWrite(t, filepath.Join(root, "android", "settings.gradle"))
	mustWrite(t, filepath.Join(root, "android", "gradlew"))
	nested := filepath.Join(root, "tools", "android", "tui")
	if err := os.MkdirAll(nested, 0o755); err != nil {
		t.Fatal(err)
	}

	got, err := Root(nested)
	if err != nil {
		t.Fatal(err)
	}
	if got != root {
		t.Fatalf("Root() = %q, want %q", got, root)
	}
}

func TestRootAcceptsWindowsWrapperMarker(t *testing.T) {
	root := t.TempDir()
	mustWrite(t, filepath.Join(root, "android", "settings.gradle"))
	mustWrite(t, filepath.Join(root, "android", "gradlew.bat"))

	got, err := Root(filepath.Join(root, "android"))
	if err != nil {
		t.Fatal(err)
	}
	if got != root {
		t.Fatalf("Root() = %q, want %q", got, root)
	}
}

func TestRootRejectsDirectoryWithoutAndroidProjectMarkers(t *testing.T) {
	if _, err := Root(t.TempDir()); err == nil {
		t.Fatal("Root() accepted a directory without Android project markers")
	}
}

func mustWrite(t *testing.T, path string) {
	t.Helper()
	if err := os.MkdirAll(filepath.Dir(path), 0o755); err != nil {
		t.Fatal(err)
	}
	if err := os.WriteFile(path, []byte("marker"), 0o755); err != nil {
		t.Fatal(err)
	}
}
