package tools

type ToolID string

const (
	ToolGo            ToolID = "go"
	ToolJava          ToolID = "java"
	ToolSDKManager    ToolID = "sdkmanager"
	ToolADB           ToolID = "adb"
	ToolPlatform37    ToolID = "platform-37"
	ToolBuildTools37  ToolID = "build-tools-37"
	ToolGradleWrapper ToolID = "gradle-wrapper"
	ToolScrcpy        ToolID = "scrcpy"
)

type Tool struct {
	ID          ToolID
	Name        string
	Description string
	Installable bool
}

func Catalog() []Tool {
	return []Tool{
		{ToolGo, "Go", "Builds and tests the Android developer TUI.", true},
		{ToolJava, "Java 17–25 JDK", "Runs Gradle, sdkmanager, and Android build tools with a supported JDK.", true},
		{ToolSDKManager, "Android SDK command-line tools", "Provides sdkmanager and Android SDK package management.", true},
		{ToolADB, "ADB / platform-tools", "Discovers, installs, launches, and diagnoses Android devices.", true},
		{ToolPlatform37, "Android platform 37", "Supplies the compile SDK required by CineLis.", true},
		{ToolBuildTools37, "Android build-tools 37.0.0", "Packages and signs the Android application.", true},
		{ToolGradleWrapper, "Gradle wrapper", "Builds the repository with its pinned Gradle version.", false},
		{ToolScrcpy, "scrcpy", "Mirrors and controls the selected Android device.", true},
	}
}

type Status struct {
	Tool    Tool
	Ready   bool
	Version string
	Path    string
	Detail  string
}
