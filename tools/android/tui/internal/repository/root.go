package repository

import (
	"errors"
	"os"
	"path/filepath"
)

func Root(starts ...string) (string, error) {
	for _, start := range starts {
		if root, ok := findRoot(start); ok {
			return root, nil
		}
	}
	return "", errors.New("could not locate the CineLis repository root")
}

func Current() (string, error) {
	starts := make([]string, 0, 2)
	if workingDirectory, err := os.Getwd(); err == nil {
		starts = append(starts, workingDirectory)
	}
	if executable, err := os.Executable(); err == nil {
		starts = append(starts, filepath.Dir(executable))
	}
	return Root(starts...)
}

func findRoot(start string) (string, bool) {
	directory, err := filepath.Abs(start)
	if err != nil {
		return "", false
	}
	if info, statErr := os.Stat(directory); statErr == nil && !info.IsDir() {
		directory = filepath.Dir(directory)
	}
	for {
		androidDirectory := filepath.Join(directory, "android")
		settings := filepath.Join(androidDirectory, "settings.gradle")
		if isFile(settings) && (isFile(filepath.Join(androidDirectory, "gradlew")) ||
			isFile(filepath.Join(androidDirectory, "gradlew.bat"))) {
			return directory, true
		}
		parent := filepath.Dir(directory)
		if parent == directory {
			return "", false
		}
		directory = parent
	}
}

func isFile(path string) bool {
	info, err := os.Stat(path)
	return err == nil && info.Mode().IsRegular()
}
