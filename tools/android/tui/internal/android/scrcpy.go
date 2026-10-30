package android

import (
	"strings"

	"github.com/rafasixteen/cinelis/tools/android/tui/internal/platform"
)

type ProcessInspector interface {
	ProcessIDs(name string) ([]int, error)
	Args(pid int) ([]string, error)
}

func ScrcpyRunning(osName platform.OS, serial string, inspector ProcessInspector) bool {
	if osName != platform.Linux || inspector == nil {
		return false
	}
	processIDs, err := inspector.ProcessIDs("scrcpy")
	if err != nil {
		return false
	}
	for _, processID := range processIDs {
		args, err := inspector.Args(processID)
		if err != nil {
			continue
		}
		for index, arg := range args {
			if (arg == "--serial" || arg == "-s") && index+1 < len(args) && args[index+1] == serial {
				return true
			}
			if strings.HasPrefix(arg, "--serial=") && strings.TrimPrefix(arg, "--serial=") == serial {
				return true
			}
			if strings.HasPrefix(arg, "-s") && arg != "-s" && strings.TrimPrefix(arg, "-s") == serial {
				return true
			}
		}
	}
	return false
}
