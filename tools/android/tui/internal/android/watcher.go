package android

import (
	"errors"
	"io/fs"
	"os"
	"path/filepath"
)

type FileState struct {
	ModificationTime int64
	Size             int64
}

func SnapshotSources(root string) (map[string]FileState, error) {
	paths := []string{
		"android/app/src",
		"android/app/build.gradle",
		"android/build.gradle",
		"android/settings.gradle",
		"android/gradle.properties",
		"android/gradle",
	}
	snapshot := make(map[string]FileState)
	for _, relative := range paths {
		path := filepath.Join(root, relative)
		info, err := os.Stat(path)
		if errors.Is(err, os.ErrNotExist) {
			continue
		}
		if err != nil {
			return nil, err
		}
		if !info.IsDir() {
			snapshot[filepath.Clean(relative)] = fileState(info)
			continue
		}
		err = filepath.WalkDir(path, func(filePath string, entry fs.DirEntry, walkErr error) error {
			if walkErr != nil {
				return walkErr
			}
			if entry.IsDir() && filePath != path && generatedDirectory(entry.Name()) {
				return filepath.SkipDir
			}
			if entry.IsDir() {
				return nil
			}
			fileInfo, err := entry.Info()
			if err != nil {
				return err
			}
			relativePath, err := filepath.Rel(root, filePath)
			if err != nil {
				return err
			}
			snapshot[filepath.Clean(relativePath)] = fileState(fileInfo)
			return nil
		})
		if err != nil {
			return nil, err
		}
	}
	return snapshot, nil
}

func generatedDirectory(name string) bool {
	return name == "build" || name == ".gradle" || name == ".cxx"
}

func fileState(info os.FileInfo) FileState {
	return FileState{ModificationTime: info.ModTime().UnixNano(), Size: info.Size()}
}
