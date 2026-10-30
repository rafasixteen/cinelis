package tools

type InstallKind string

const (
	InstallPackage          InstallKind = "package"
	InstallCommandLineTools InstallKind = "android-command-line-tools"
	InstallSDKPackage       InstallKind = "sdk-package"
	InstallDiagnostic       InstallKind = "diagnostic"
)

type InstallStep struct {
	ToolID    ToolID
	Kind      InstallKind
	BlockedBy []ToolID
}

func BuildInstallPlan(statuses []Status) []InstallStep {
	ready := make(map[ToolID]bool, len(statuses))
	for _, status := range statuses {
		ready[status.Tool.ID] = status.Ready
	}
	steps := make([]InstallStep, 0)
	appendMissing := func(id ToolID, kind InstallKind, blockedBy ...ToolID) {
		if !ready[id] {
			steps = append(steps, InstallStep{ToolID: id, Kind: kind, BlockedBy: blockedBy})
		}
	}
	appendMissing(ToolGo, InstallPackage)
	appendMissing(ToolJava, InstallPackage)
	appendMissing(ToolSDKManager, InstallCommandLineTools, ToolJava)
	appendMissing(ToolADB, InstallSDKPackage, ToolJava, ToolSDKManager)
	appendMissing(ToolPlatform37, InstallSDKPackage, ToolJava, ToolSDKManager)
	appendMissing(ToolBuildTools37, InstallSDKPackage, ToolJava, ToolSDKManager)
	appendMissing(ToolGradleWrapper, InstallDiagnostic)
	appendMissing(ToolScrcpy, InstallPackage)
	return steps
}
