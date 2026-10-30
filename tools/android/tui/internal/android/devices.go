package android

import "strings"

type Device struct {
	Serial     string
	State      string
	Label      string
	Selectable bool
	Diagnostic string
}

func ParseADBDevices(output []byte) []Device {
	lines := strings.Split(strings.ReplaceAll(string(output), "\r\n", "\n"), "\n")
	devices := make([]Device, 0)
	for _, line := range lines {
		fields := strings.Fields(line)
		if len(fields) < 2 || fields[0] == "List" || strings.HasPrefix(fields[0], "*") {
			continue
		}
		device := Device{Serial: fields[0], State: fields[1]}
		model := ""
		for _, field := range fields[2:] {
			if strings.HasPrefix(field, "model:") {
				model = strings.ReplaceAll(strings.TrimPrefix(field, "model:"), "_", " ")
			}
		}
		if model == "" {
			model = "Android device"
		}
		device.Label = model + " (" + device.Serial + ")"
		switch device.State {
		case "device":
			device.Selectable = true
		case "unauthorized":
			device.Diagnostic = "Authorize USB debugging on the device."
		case "offline":
			device.Diagnostic = "Reconnect the device or restart its emulator."
		default:
			device.Diagnostic = "Device is unavailable: " + device.State
		}
		devices = append(devices, device)
	}
	return devices
}
