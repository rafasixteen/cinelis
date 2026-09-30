.PHONY: help
help:
	@echo "Usage:"
	@echo "  make <target>"
	@echo ""
	@echo "Targets:"
	@echo "  api/<target>            Run target in apps/api"
	@echo "  backoffice/<target>     Run target in apps/backoffice"
	@echo "  frontoffice/<target>    Run target in apps/frontoffice"
	@echo "  infra/<target>          Run target in infra"
	@echo ""
	@echo "  migrate                 Run pending database migrations"
	@echo "  migrate/create NAME=x   Create a new migration"
	@echo "  migrate/down            Revert the last migration"
	@echo "  migrate/status          Show migration history"

.PHONY: api/%
api/%:
	$(MAKE) -C apps/api $*

.PHONY: backoffice/%
backoffice/%:
	$(MAKE) -C apps/backoffice $*

.PHONY: frontoffice/%
frontoffice/%:
	$(MAKE) -C apps/frontoffice $*

.PHONY: infra/%
infra/%:
	$(MAKE) -C infra $*

.PHONY: migrate
migrate:
	php yii migrate

.PHONY: migrate/create
migrate/create:
	php yii migrate/create $(NAME)

.PHONY: migrate/down
migrate/down:
	php yii migrate/down

.PHONY: migrate/status
migrate/status:
	php yii migrate/history