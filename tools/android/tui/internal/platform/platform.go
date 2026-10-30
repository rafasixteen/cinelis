package platform

import "runtime"

type OS string

const (
	Linux   OS = "linux"
	Windows OS = "windows"
)

type Command struct {
	Name string
	Args []string
	Dir  string
	Env  map[string]string
}

func CurrentOS() OS {
	if runtime.GOOS == "windows" {
		return Windows
	}
	return Linux
}
