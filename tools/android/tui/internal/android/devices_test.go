package android

import "testing"

func TestParseADBDevicesLabelsAuthorizedPhysicalAndEmulatorDevices(t *testing.T) {
	output := []byte("List of devices attached\n" +
		"emulator-5554 device product:sdk model:Pixel_7 device:emu transport_id:1\n" +
		"W8L7 device usb:1-1 product:CPH model:CPH2603 device:OP5 transport_id:2\n")
	devices := ParseADBDevices(output)
	if len(devices) != 2 {
		t.Fatalf("len(devices) = %d, want 2", len(devices))
	}
	if !devices[0].Selectable || devices[0].Label != "Pixel 7 (emulator-5554)" {
		t.Fatalf("emulator = %#v", devices[0])
	}
	if !devices[1].Selectable || devices[1].Label != "CPH2603 (W8L7)" {
		t.Fatalf("physical device = %#v", devices[1])
	}
}

func TestParseADBDevicesKeepsUnavailableDevicesDiagnosticOnly(t *testing.T) {
	output := []byte("List of devices attached\n" +
		"USB1 unauthorized usb:1-1 transport_id:1\n" +
		"emulator-5556 offline transport_id:2\n")
	devices := ParseADBDevices(output)
	if len(devices) != 2 {
		t.Fatalf("len(devices) = %d, want 2", len(devices))
	}
	for _, device := range devices {
		if device.Selectable {
			t.Fatalf("%s was selectable in state %s", device.Serial, device.State)
		}
		if device.Diagnostic == "" {
			t.Fatalf("%s has no diagnostic", device.Serial)
		}
	}
}
