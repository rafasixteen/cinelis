#!/usr/bin/env bash

set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="${CINELIS_ENV_FILE:-$ROOT_DIR/.env}"

# Use production unless another self-contained Compose stack is requested.
DEPLOY_TARGET="${1:-prod}"
COMPOSE_FILE="$ROOT_DIR/infra/$DEPLOY_TARGET/compose.yml"

# Keep the target inside infra/ and prevent path traversal through the argument.
if [[ ! "$DEPLOY_TARGET" =~ ^[a-z0-9_-]+$ ]]; then
	printf 'Invalid deployment target: %s\n' "$DEPLOY_TARGET" >&2
	exit 1
fi

# Fail before pulling or changing containers when the requested stack is absent.
if [[ ! -f "$COMPOSE_FILE" ]]; then
	printf 'Missing Compose file for deployment target "%s": %s\n' \
		"$DEPLOY_TARGET" "$COMPOSE_FILE" >&2
	exit 1
fi

if [[ ! -f "$ENV_FILE" ]]; then
	printf 'Missing environment file: %s\n' "$ENV_FILE" >&2
	exit 1
fi

# Deploy only from a clean checkout so local files cannot be overwritten by pull.
if [[ "$(git -C "$ROOT_DIR" status --porcelain)" != '' ]]; then
	printf 'The repository has uncommitted changes. Refusing to deploy.\n' >&2
	exit 1
fi

set -a
# shellcheck disable=SC1090
# Export Compose variables and deployment settings to the commands below.
. "$ENV_FILE"
set +a

# The deployment server follows main; feature branches must be merged first.
if [[ "$(git -C "$ROOT_DIR" branch --show-current)" != 'main' ]]; then
	printf 'The deployment checkout must be on the main branch.\n' >&2
	exit 1
fi

compose() {
	# Centralize the selected environment file and Compose file for every call.
	docker compose \
		--env-file "$ENV_FILE" \
		-f "$COMPOSE_FILE" \
		"$@"
}

on_error() {
	# Preserve useful failure context without hiding the original error.
	printf '\nDeployment failed. Current service state:\n' >&2
	compose ps >&2 || true
}

trap on_error ERR

# Update only when fast-forwarding is possible; never rewrite the deployment checkout.
printf 'Updating the deployment checkout...\n'
git -C "$ROOT_DIR" pull --ff-only origin main

# Catch missing variables and invalid service definitions before building anything.
printf 'Validating the %s Compose configuration...\n' "$DEPLOY_TARGET"
compose config --quiet

# Build every image declared by the selected stack, including newly added services.
printf 'Building %s images...\n' "$DEPLOY_TARGET"
compose build --pull

# Database operations below require a healthy PostgreSQL service first.
printf 'Starting PostgreSQL and waiting for health...\n'
compose up -d --wait postgres

BACKUP_DIR="${CINELIS_BACKUP_DIR:-$ROOT_DIR/.backups}"
BACKUP_FILE="$BACKUP_DIR/postgres-$(date -u +%Y%m%dT%H%M%SZ).dump"

mkdir -p "$BACKUP_DIR"

# Take a recoverable backup before migrations change the database schema.
printf 'Creating database backup: %s\n' "$BACKUP_FILE"
compose exec -T postgres sh -c \
	'pg_dump -U "$POSTGRES_USER" -d "$POSTGRES_DB" --format=custom' \
	> "$BACKUP_FILE"

# Apply pending schema changes from inside the console image.
printf 'Running database migrations...\n'
compose run --rm --no-deps console php yii migrate --interactive=0

# Seed/update authorization rules before application containers accept traffic.
printf 'Ensuring RBAC definitions exist...\n'
compose run --rm --no-deps console php yii rbac/init

# Recreate application services with the newly built images and wait for startup.
printf 'Starting application services...\n'
compose up -d --wait --remove-orphans \
	api \
	backoffice \
	frontoffice \
	caddy

# Verify the public routing path, PHP application, and database-backed API together.
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
