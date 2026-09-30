.PHONY: help
help:
	@echo "Usage:"
	@echo "  make <target>"
	@echo ""
	@echo "Targets:"
	@echo "  up        Start Dekit tasks"
	@echo "  dev       Start Dekit tasks and attach to the TUI"
	@echo "  down      Stop Dekit tasks"

.PHONY: up
up:
	dekit up

.PHONY: dev
dev:
	dekit up
	dekit attach

.PHONY: down
down:
	dekit down