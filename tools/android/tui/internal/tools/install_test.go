package tools

import (
	"reflect"
	"testing"
)

func TestBuildInstallPlanSkipsReadyToolsAndPreservesDependencies(t *testing.T) {
	statuses := statusesWithReady(ToolGo)
	plan := BuildInstallPlan(statuses)
	want := []InstallStep{
		{ToolID: ToolJava, Kind: InstallPackage},
		{ToolID: ToolSDKManager, Kind: InstallCommandLineTools, BlockedBy: []ToolID{ToolJava}},
		{ToolID: ToolADB, Kind: InstallSDKPackage, BlockedBy: []ToolID{ToolJava, ToolSDKManager}},
		{ToolID: ToolPlatform37, Kind: InstallSDKPackage, BlockedBy: []ToolID{ToolJava, ToolSDKManager}},
		{ToolID: ToolBuildTools37, Kind: InstallSDKPackage, BlockedBy: []ToolID{ToolJava, ToolSDKManager}},
		{ToolID: ToolGradleWrapper, Kind: InstallDiagnostic},
		{ToolID: ToolScrcpy, Kind: InstallPackage},
	}
	if !reflect.DeepEqual(plan, want) {
		t.Fatalf("BuildInstallPlan() = %#v, want %#v", plan, want)
	}
}

func TestInstallPlanKeepsScrcpyIndependentFromJavaAndSDK(t *testing.T) {
	plan := BuildInstallPlan(statusesWithReady())
	var scrcpy InstallStep
	for _, step := range plan {
		if step.ToolID == ToolScrcpy {
			scrcpy = step
		}
	}
	if len(scrcpy.BlockedBy) != 0 {
		t.Fatalf("scrcpy dependencies = %v", scrcpy.BlockedBy)
	}
}

func statusesWithReady(ready ...ToolID) []Status {
	readySet := make(map[ToolID]bool, len(ready))
	for _, id := range ready {
		readySet[id] = true
	}
	statuses := make([]Status, 0, len(Catalog()))
	for _, tool := range Catalog() {
		statuses = append(statuses, Status{Tool: tool, Ready: readySet[tool.ID]})
	}
	return statuses
}
