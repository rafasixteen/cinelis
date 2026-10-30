package tools

import (
	"archive/zip"
	"fmt"
	"io"
	"os"
	"path/filepath"
	"strings"
)

func ExtractZip(reader io.ReaderAt, size int64, destination string) error {
	archive, err := zip.NewReader(reader, size)
	if err != nil {
		return fmt.Errorf("open ZIP archive: %w", err)
	}
	cleanDestination, err := filepath.Abs(destination)
	if err != nil {
		return fmt.Errorf("resolve extraction destination: %w", err)
	}
	if err := os.MkdirAll(cleanDestination, 0o755); err != nil {
		return fmt.Errorf("create extraction destination: %w", err)
	}
	for _, entry := range archive.File {
		if err := extractZipEntry(entry, cleanDestination); err != nil {
			return err
		}
	}
	return nil
}

func extractZipEntry(entry *zip.File, destination string) error {
	if filepath.IsAbs(entry.Name) || strings.Contains(entry.Name, "\\") {
		return fmt.Errorf("unsafe ZIP entry %q", entry.Name)
	}
	cleanName := filepath.Clean(entry.Name)
	if cleanName == ".." || strings.HasPrefix(cleanName, ".."+string(filepath.Separator)) {
		return fmt.Errorf("unsafe ZIP entry %q", entry.Name)
	}
	if entry.Mode()&os.ModeSymlink != 0 {
		return fmt.Errorf("ZIP entry %q is a symbolic link", entry.Name)
	}
	target := filepath.Join(destination, cleanName)
	relative, err := filepath.Rel(destination, target)
	if err != nil || relative == ".." || strings.HasPrefix(relative, ".."+string(filepath.Separator)) {
		return fmt.Errorf("ZIP entry %q escapes its destination", entry.Name)
	}
	if entry.FileInfo().IsDir() {
		return os.MkdirAll(target, 0o755)
	}
	if !entry.Mode().IsRegular() {
		return fmt.Errorf("ZIP entry %q is not a regular file", entry.Name)
	}
	if err := os.MkdirAll(filepath.Dir(target), 0o755); err != nil {
		return fmt.Errorf("create directory for %q: %w", entry.Name, err)
	}
	source, err := entry.Open()
	if err != nil {
		return fmt.Errorf("open ZIP entry %q: %w", entry.Name, err)
	}
	defer source.Close()
	mode := entry.Mode().Perm()
	if mode == 0 {
		mode = 0o644
	}
	destinationFile, err := os.OpenFile(target, os.O_CREATE|os.O_EXCL|os.O_WRONLY, mode)
	if err != nil {
		return fmt.Errorf("create ZIP entry %q: %w", entry.Name, err)
	}
	_, copyErr := io.Copy(destinationFile, source)
	closeErr := destinationFile.Close()
	if copyErr != nil {
		return fmt.Errorf("extract ZIP entry %q: %w", entry.Name, copyErr)
	}
	if closeErr != nil {
		return fmt.Errorf("close ZIP entry %q: %w", entry.Name, closeErr)
	}
	return nil
}
