package tools

import (
	"context"
	"crypto/sha256"
	"fmt"
	"io"
	"net/http"
	"os"
	"path/filepath"
	"strings"
	"time"

	"github.com/rafasixteen/cinelis/tools/android/tui/internal/platform"
)

const commandLineToolsMaxBytes int64 = 1024 * 1024 * 1024

type Archive struct {
	URL      string
	SHA256   string
	MaxBytes int64
}

func CommandLineToolsArchive(osName platform.OS) (Archive, error) {
	switch osName {
	case platform.Linux:
		return Archive{
			URL:      "https://dl.google.com/android/repository/commandlinetools-linux-15859902_latest.zip",
			SHA256:   "4e4c464f145a7512b57d088ac6c278c03c9eea610886b35a5e0804e74eedf583",
			MaxBytes: commandLineToolsMaxBytes,
		}, nil
	case platform.Windows:
		return Archive{
			URL:      "https://dl.google.com/android/repository/commandlinetools-win-15859902_latest.zip",
			SHA256:   "90ae805d20434428bffcb699c290860f19bb5f66a67e6b330067e3de801fb04a",
			MaxBytes: commandLineToolsMaxBytes,
		}, nil
	default:
		return Archive{}, fmt.Errorf("Android command-line tools are not configured for %q", osName)
	}
}

type Downloader interface {
	Download(context.Context, Archive, string) error
}

type HTTPDownloader struct {
	Client *http.Client
}

func (downloader HTTPDownloader) Download(context context.Context, archive Archive, destination string) (resultError error) {
	client := downloader.Client
	if client == nil {
		client = &http.Client{Timeout: 10 * time.Minute}
	}
	request, err := http.NewRequestWithContext(context, http.MethodGet, archive.URL, nil)
	if err != nil {
		return fmt.Errorf("create download request: %w", err)
	}
	response, err := client.Do(request)
	if err != nil {
		return fmt.Errorf("download Android tools: %w", err)
	}
	defer response.Body.Close()
	if response.StatusCode < 200 || response.StatusCode >= 300 {
		return fmt.Errorf("download Android tools: unexpected HTTP status %s", response.Status)
	}
	if archive.MaxBytes <= 0 {
		return fmt.Errorf("download size limit must be positive")
	}
	if response.ContentLength > archive.MaxBytes {
		return fmt.Errorf("download exceeds the %d-byte limit", archive.MaxBytes)
	}
	partial := destination + ".partial"
	if err := os.MkdirAll(filepath.Dir(destination), 0o755); err != nil {
		return fmt.Errorf("create download directory: %w", err)
	}
	file, err := os.OpenFile(partial, os.O_CREATE|os.O_TRUNC|os.O_WRONLY, 0o600)
	if err != nil {
		return fmt.Errorf("create partial download: %w", err)
	}
	defer func() {
		_ = file.Close()
		if resultError != nil {
			_ = os.Remove(partial)
		}
	}()
	hash := sha256.New()
	written, err := io.Copy(io.MultiWriter(file, hash), io.LimitReader(response.Body, archive.MaxBytes+1))
	if err != nil {
		return fmt.Errorf("write download: %w", err)
	}
	if written > archive.MaxBytes {
		return fmt.Errorf("download exceeds the %d-byte limit", archive.MaxBytes)
	}
	if err := file.Close(); err != nil {
		return fmt.Errorf("finish download: %w", err)
	}
	digest := fmt.Sprintf("%x", hash.Sum(nil))
	if !strings.EqualFold(digest, archive.SHA256) {
		return fmt.Errorf("download checksum mismatch: got %s", digest)
	}
	if err := os.Rename(partial, destination); err != nil {
		return fmt.Errorf("publish verified download: %w", err)
	}
	return nil
}

type SDKInstaller struct {
	OS         platform.OS
	SDKRoot    string
	Downloader Downloader
	TempDir    func() (string, error)
	Now        func() time.Time
}

func (installer SDKInstaller) InstallCommandLineTools(context context.Context) (string, error) {
	archive, err := CommandLineToolsArchive(installer.OS)
	if err != nil {
		return "", err
	}
	if installer.Downloader == nil {
		return "", fmt.Errorf("Android tools downloader is not configured")
	}
	parent := filepath.Join(installer.SDKRoot, "cmdline-tools")
	if err := os.MkdirAll(parent, 0o755); err != nil {
		return "", fmt.Errorf("create command-line tools directory: %w", err)
	}
	tempDir := installer.TempDir
	if tempDir == nil {
		tempDir = func() (string, error) { return os.MkdirTemp(parent, ".install-") }
	}
	workingDirectory, err := tempDir()
	if err != nil {
		return "", fmt.Errorf("create installation staging directory: %w", err)
	}
	defer os.RemoveAll(workingDirectory)
	archivePath := filepath.Join(workingDirectory, "command-line-tools.zip")
	if err := installer.Downloader.Download(context, archive, archivePath); err != nil {
		return "", err
	}
	archiveFile, err := os.Open(archivePath)
	if err != nil {
		return "", fmt.Errorf("open downloaded archive: %w", err)
	}
	archiveInfo, err := archiveFile.Stat()
	if err != nil {
		archiveFile.Close()
		return "", fmt.Errorf("inspect downloaded archive: %w", err)
	}
	extracted := filepath.Join(workingDirectory, "extracted")
	if err := ExtractZip(archiveFile, archiveInfo.Size(), extracted); err != nil {
		archiveFile.Close()
		return "", err
	}
	if err := archiveFile.Close(); err != nil {
		return "", fmt.Errorf("close downloaded archive: %w", err)
	}
	staged := filepath.Join(extracted, "cmdline-tools")
	sdkManager := filepath.Join(staged, "bin", ExecutableName(installer.OS, "sdkmanager"))
	if !regularFile(sdkManager) {
		return "", fmt.Errorf("downloaded archive does not contain %s", sdkManager)
	}
	latest := filepath.Join(parent, "latest")
	backup := ""
	if _, err := os.Stat(latest); err == nil {
		now := time.Now
		if installer.Now != nil {
			now = installer.Now
		}
		backup = filepath.Join(parent, "latest.backup-"+now().UTC().Format("20060102T150405Z"))
		if _, err := os.Stat(backup); err == nil {
			return "", fmt.Errorf("backup destination already exists: %s", backup)
		}
		if err := os.Rename(latest, backup); err != nil {
			return "", fmt.Errorf("back up existing command-line tools: %w", err)
		}
	} else if !os.IsNotExist(err) {
		return "", fmt.Errorf("inspect existing command-line tools: %w", err)
	}
	if err := os.Rename(staged, latest); err != nil {
		if backup != "" {
			_ = os.Rename(backup, latest)
		}
		return "", fmt.Errorf("publish command-line tools: %w", err)
	}
	return backup, nil
}

func SDKPackageCommand(osName platform.OS, sdkRoot string, toolID ToolID) platform.Command {
	modern := filepath.Join(sdkRoot, "cmdline-tools", "latest", "bin", ExecutableName(osName, "android"))
	if regularFile(modern) {
		packages := map[ToolID]string{
			ToolADB:          "platform-tools",
			ToolPlatform37:   "platforms/android-37.0",
			ToolBuildTools37: "build-tools/37.0.0",
		}
		return platform.Command{
			Name: modern,
			Args: []string{"sdk", "install", packages[toolID]},
			Env:  map[string]string{"ANDROID_HOME": sdkRoot, "ANDROID_SDK_ROOT": sdkRoot},
		}
	}
	packages := map[ToolID]string{
		ToolADB:          "platform-tools",
		ToolPlatform37:   "platforms;android-37",
		ToolBuildTools37: "build-tools;37.0.0",
	}
	return platform.Command{
		Name: filepath.Join(sdkRoot, "cmdline-tools", "latest", "bin", ExecutableName(osName, "sdkmanager")),
		Args: []string{packages[toolID]},
		Env:  map[string]string{"ANDROID_HOME": sdkRoot, "ANDROID_SDK_ROOT": sdkRoot},
	}
}

func SDKLicensesCommand(osName platform.OS, sdkRoot string) platform.Command {
	modern := filepath.Join(sdkRoot, "cmdline-tools", "latest", "bin", ExecutableName(osName, "android"))
	if regularFile(modern) {
		return platform.Command{}
	}
	command := SDKPackageCommand(osName, sdkRoot, "")
	command.Args = []string{"--licenses"}
	return command
}
