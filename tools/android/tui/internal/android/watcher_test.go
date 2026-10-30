package android

import (
	"os"
	"path/filepath"
	"reflect"
	"testing"
	"time"
)

func TestSnapshotSourcesDetectsRelevantChangesAndSkipsGeneratedBuildFiles(t *testing.T) {
	root := t.TempDir()
	source := filepath.Join(root, "android", "app", "src", "main", "MainActivity.java")
	generated := filepath.Join(root, "android", "app", "src", "build", "generated.java")
	writeWatchFile(t, source, "one")
	writeWatchFile(t, generated, "generated one")
	first, err := SnapshotSources(root)
	if err != nil {
		t.Fatal(err)
	}
	if _, exists := first[filepath.Clean("android/app/src/build/generated.java")]; exists {
		t.Fatal("SnapshotSources included generated build output")
	}
	time.Sleep(time.Millisecond)
	writeWatchFile(t, source, "two with another size")
	second, err := SnapshotSources(root)
	if err != nil {
		t.Fatal(err)
	}
	if reflect.DeepEqual(first, second) {
		t.Fatal("SnapshotSources did not detect a source change")
	}
}

func writeWatchFile(t *testing.T, path, value string) {
	t.Helper()
	if err := os.MkdirAll(filepath.Dir(path), 0o755); err != nil {
		t.Fatal(err)
	}
	if err := os.WriteFile(path, []byte(value), 0o644); err != nil {
		t.Fatal(err)
	}
}
