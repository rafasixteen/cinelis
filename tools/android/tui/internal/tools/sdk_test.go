package tools

import (
	"context"
	"crypto/sha256"
	"fmt"
	"net/http"
	"net/http/httptest"
	"os"
	"path/filepath"
	"strings"
	"testing"
	"time"

	"github.com/rafasixteen/cinelis/tools/android/tui/internal/platform"
)

func TestCommandLineToolsArchiveIsPinnedForLinuxAndWindows(t *testing.T) {
	linux, err := CommandLineToolsArchive(platform.Linux)
	if err != nil {
		t.Fatal(err)
	}
	if !strings.Contains(linux.URL, "commandlinetools-linux-15859902_latest.zip") ||
		linux.SHA256 != "4e4c464f145a7512b57d088ac6c278c03c9eea610886b35a5e0804e74eedf583" {
		t.Fatalf("unexpected Linux archive: %+v", linux)
	}
	windows, err := CommandLineToolsArchive(platform.Windows)
	if err != nil {
		t.Fatal(err)
	}
	if !strings.Contains(windows.URL, "commandlinetools-win-15859902_latest.zip") ||
		windows.SHA256 != "90ae805d20434428bffcb699c290860f19bb5f66a67e6b330067e3de801fb04a" {
		t.Fatalf("unexpected Windows archive: %+v", windows)
	}
}

func TestHTTPDownloaderRejectsStatusSizeAndChecksumAndCleansPartialFile(t *testing.T) {
	tests := []struct {
		name       string
		statusCode int
		body       string
		maxBytes   int64
		checksum   string
	}{
		{"status", http.StatusNotFound, "no", 100, strings.Repeat("0", 64)},
		{"size", http.StatusOK, "too large", 3, fmt.Sprintf("%x", sha256.Sum256([]byte("too large")))},
		{"checksum", http.StatusOK, "payload", 100, strings.Repeat("0", 64)},
	}
	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, _ *http.Request) {
				w.WriteHeader(test.statusCode)
				_, _ = w.Write([]byte(test.body))
			}))
			defer server.Close()
			destination := filepath.Join(t.TempDir(), "archive.zip")
			downloader := HTTPDownloader{Client: &http.Client{Timeout: time.Second}}
			err := downloader.Download(context.Background(), Archive{URL: server.URL, SHA256: test.checksum, MaxBytes: test.maxBytes}, destination)
			if err == nil {
				t.Fatal("expected download failure")
			}
			if _, statErr := os.Stat(destination + ".partial"); !os.IsNotExist(statErr) {
				t.Fatalf("partial download was not cleaned: %v", statErr)
			}
		})
	}
}

func TestHTTPDownloaderWritesVerifiedPayload(t *testing.T) {
	payload := []byte("verified")
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, _ *http.Request) {
		_, _ = w.Write(payload)
	}))
	defer server.Close()
	destination := filepath.Join(t.TempDir(), "archive.zip")
	digest := fmt.Sprintf("%x", sha256.Sum256(payload))
	if err := (HTTPDownloader{}).Download(context.Background(), Archive{URL: server.URL, SHA256: digest, MaxBytes: 100}, destination); err != nil {
		t.Fatal(err)
	}
	contents, _ := os.ReadFile(destination)
	if string(contents) != string(payload) {
		t.Fatalf("download = %q", contents)
	}
}

func TestSDKInstallerStagesLatestAndBacksUpExistingInstall(t *testing.T) {
	sdkRoot := t.TempDir()
	latest := filepath.Join(sdkRoot, "cmdline-tools", "latest")
	if err := os.MkdirAll(latest, 0o755); err != nil {
		t.Fatal(err)
	}
	if err := os.WriteFile(filepath.Join(latest, "old"), []byte("old"), 0o644); err != nil {
		t.Fatal(err)
	}
	archive := zipBytes(t, zipEntry{name: "cmdline-tools/bin/sdkmanager", body: "new", mode: 0o755})
	temporaryParent := t.TempDir()
	downloader := fakeDownloader{archive: archive}
	installer := SDKInstaller{
		OS: platform.Linux, SDKRoot: sdkRoot, Downloader: downloader,
		TempDir: func() (string, error) { return os.MkdirTemp(temporaryParent, "install-") },
		Now:     func() time.Time { return time.Date(2026, 10, 30, 12, 0, 0, 0, time.UTC) },
	}
	backup, err := installer.InstallCommandLineTools(context.Background())
	if err != nil {
		t.Fatal(err)
	}
	if backup != filepath.Join(sdkRoot, "cmdline-tools", "latest.backup-20261030T120000Z") {
		t.Fatalf("backup = %q", backup)
	}
	if _, err := os.Stat(filepath.Join(backup, "old")); err != nil {
		t.Fatalf("backup missing: %v", err)
	}
	if contents, err := os.ReadFile(filepath.Join(latest, "bin", "sdkmanager")); err != nil || string(contents) != "new" {
		t.Fatalf("latest contents = %q, %v", contents, err)
	}
	entries, _ := os.ReadDir(temporaryParent)
	if len(entries) != 0 {
		t.Fatalf("temporary directories were not cleaned: %v", entries)
	}
}

func TestSDKManagerCommandsAreExactAndLicensesStayInteractive(t *testing.T) {
	root := filepath.Join("SDK With Spaces")
	command := SDKPackageCommand(platform.Windows, root, ToolBuildTools37)
	if command.Name != filepath.Join(root, "cmdline-tools", "latest", "bin", "sdkmanager.bat") ||
		len(command.Args) != 1 || command.Args[0] != "build-tools;37.0.0" {
		t.Fatalf("build tools command = %#v", command)
	}
	licenses := SDKLicensesCommand(platform.Linux, root)
	if len(licenses.Args) != 1 || licenses.Args[0] != "--licenses" {
		t.Fatalf("licenses command = %#v", licenses)
	}
	if got := SDKPackageCommand(platform.Linux, root, ToolADB).Args[0]; got != "platform-tools" {
		t.Fatalf("ADB package = %q", got)
	}
	if got := SDKPackageCommand(platform.Linux, root, ToolPlatform37).Args[0]; got != "platforms;android-37" {
		t.Fatalf("platform package = %q", got)
	}
}

func TestSDKCommandsUseModernAndroidCLIWhenItIsInstalled(t *testing.T) {
	root := t.TempDir()
	modern := filepath.Join(root, "cmdline-tools", "latest", "bin", "android")
	if err := os.MkdirAll(filepath.Dir(modern), 0o755); err != nil {
		t.Fatal(err)
	}
	if err := os.WriteFile(modern, []byte("android"), 0o755); err != nil {
		t.Fatal(err)
	}
	command := SDKPackageCommand(platform.Linux, root, ToolPlatform37)
	if command.Name != modern || len(command.Args) != 3 || command.Args[0] != "sdk" || command.Args[1] != "install" || command.Args[2] != "platforms/android-37.0" {
		t.Fatalf("modern platform command = %#v", command)
	}
	licenses := SDKLicensesCommand(platform.Linux, root)
	if licenses.Name != "" || len(licenses.Args) != 0 {
		t.Fatalf("modern license command should be skipped: %#v", licenses)
	}
}

type fakeDownloader struct{ archive []byte }

func (downloader fakeDownloader) Download(_ context.Context, _ Archive, destination string) error {
	return os.WriteFile(destination, downloader.archive, 0o644)
}
