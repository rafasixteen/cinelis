#!/usr/bin/env bash

set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="${CINELIS_ENV_FILE:-$ROOT_DIR/.env}"

if [[ ! -f "$ENV_FILE" ]]; then
	printf 'Missing production environment file: %s\n' "$ENV_FILE" >&2
	exit 1
fi

if [[ "$(git -C "$ROOT_DIR" status --porcelain)" != '' ]]; then
	printf 'The repository has uncommitted changes. Refusing to deploy.\n' >&2
	exit 1
fi

set -a
# shellcheck disable=SC1090
. "$ENV_FILE"
set +a

if [[ "$(git -C "$ROOT_DIR" branch --show-current)" != 'main' ]]; then
	printf 'The deployment checkout must be on the main branch.\n' >&2
	exit 1
fi

compose() {
	docker compose \
		--env-file "$ENV_FILE" \
		-f "$ROOT_DIR/infra/prod/compose.yml" \
		"$@"
}

on_error() {
	printf '\nDeployment failed. Current service state:\n' >&2
	compose ps >&2 || true
}

trap on_error ERR

printf 'Updating the deployment checkout...\n'
git -C "$ROOT_DIR" pull --ff-only origin main

printf 'Validating the production Compose configuration...\n'
compose config --quiet

printf 'Building production images...\n'
compose build --pull \
	api \
	backoffice \
	frontoffice \
	console \
	caddy

printf 'Starting PostgreSQL and waiting for health...\n'
compose up -d --wait postgres

BACKUP_DIR="${CINELIS_BACKUP_DIR:-$ROOT_DIR/.backups}"
mkdir -p "$BACKUP_DIR"
BACKUP_FILE="$BACKUP_DIR/postgres-$(date -u +%Y%m%dT%H%M%SZ).dump"

printf 'Creating database backup: %s\n' "$BACKUP_FILE"
compose exec -T postgres sh -c \
	'pg_dump -U "$POSTGRES_USER" -d "$POSTGRES_DB" --format=custom' \
	> "$BACKUP_FILE"

printf 'Running database migrations...\n'
compose run --rm --no-deps console php yii migrate --interactive=0

printf 'Ensuring RBAC definitions exist...\n'
compose run --rm --no-deps console php yii rbac/init

printf 'Starting application services...\n'
compose up -d --wait --remove-orphans \
	api \
	backoffice \
	frontoffice \
	caddy

HEALTH_URL="${CADDY_URL}/api/health"
printf 'Checking application health: %s\n' "$HEALTH_URL"
curl --fail --silent --show-error \
	--retry 10 \
	--retry-all-errors \
	--retry-delay 2 \
	"$HEALTH_URL" \
	> /dev/null

printf '\nDeployment completed.\n'
compose ps
