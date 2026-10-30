package tools

import "testing"

func TestCatalogListsEveryDependencyInInstallOrderWithDescriptions(t *testing.T) {
	want := []ToolID{ToolGo, ToolJava, ToolSDKManager, ToolADB, ToolPlatform37, ToolBuildTools37, ToolGradleWrapper, ToolScrcpy}
	got := Catalog()
	if len(got) != len(want) {
		t.Fatalf("Catalog length = %d, want %d", len(got), len(want))
	}
	for index, id := range want {
		if got[index].ID != id {
			t.Fatalf("Catalog[%d].ID = %q, want %q", index, got[index].ID, id)
		}
		if got[index].Description == "" {
			t.Fatalf("Catalog[%d] has no description", index)
		}
	}
	if got[6].Installable {
		t.Fatal("Gradle wrapper must be diagnostic-only")
	}
}
