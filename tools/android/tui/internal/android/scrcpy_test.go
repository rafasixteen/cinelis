package android

import (
	"reflect"
	"testing"

	"github.com/rafasixteen/cinelis/tools/android/tui/internal/platform"
)

type fakeInspector struct {
	pids  []int
	args  map[int][]string
	calls int
}

func (f *fakeInspector) ProcessIDs(string) ([]int, error) { f.calls++; return f.pids, nil }
func (f *fakeInspector) Args(pid int) ([]string, error)   { return f.args[pid], nil }

func TestScrcpyCommandSupportsLinuxAndWindowsWithoutShells(t *testing.T) {
	for _, osName := range []platform.OS{platform.Linux, platform.Windows} {
		got := ScrcpyCommand("scrcpy", "emulator-5554")
		want := platform.Command{Name: "scrcpy", Args: []string{"--serial", "emulator-5554", "--stay-awake"}}
		if !reflect.DeepEqual(got, want) {
			t.Fatalf("%s ScrcpyCommand() = %#v", osName, got)
		}
	}
}

func TestScrcpyReuseDetectionIsLinuxOnlyAndMatchesSelectedSerial(t *testing.T) {
	inspector := &fakeInspector{pids: []int{10, 20}, args: map[int][]string{
		10: {"scrcpy", "--serial", "other"}, 20: {"scrcpy", "--serial=emulator-5554"},
	}}
	if !ScrcpyRunning(platform.Linux, "emulator-5554", inspector) {
		t.Fatal("ScrcpyRunning did not match Linux process arguments")
	}
	inspector.calls = 0
	if ScrcpyRunning(platform.Windows, "emulator-5554", inspector) {
		t.Fatal("ScrcpyRunning claimed Windows process reuse")
	}
	if inspector.calls != 0 {
		t.Fatal("ScrcpyRunning inspected processes on Windows")
	}
}
