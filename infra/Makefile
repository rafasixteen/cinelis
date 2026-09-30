COMPOSE := docker compose -f compose.yml

.PHONY: help
help:
	@echo "Usage:"
	@echo "  make <target>"
	@echo ""
	@echo "Targets:"
	@echo "  up        Start infrastructure"
	@echo "  down      Stop infrastructure"
	@echo "  restart   Restart infrastructure"
	@echo "  logs      Follow infrastructure logs"
	@echo "  ps        Show infrastructure status"

.PHONY: up
up:
	$(COMPOSE) up -d

.PHONY: down
down:
	$(COMPOSE) down

.PHONY: restart
restart:
	$(COMPOSE) restart

.PHONY: logs
logs:
	$(COMPOSE) logs -f

.PHONY: ps
ps:
	$(COMPOSE) ps