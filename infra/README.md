# Cinelis Infrastructure

This directory contains the local infrastructure used by Cinelis during development.

Application processes such as the API, frontoffice and backoffice run directly on the host. Docker is used only for infrastructure services.

## Structure

```text
infra/
├── services/
│   └── postgres.yml
├── compose.yml
├── Makefile
└── README.md
```

`compose.yml` is the main Docker Compose entrypoint.

Individual infrastructure services are defined under:

```text
services/
```

This keeps each service isolated and makes the Compose configuration easier to extend.

## Current services

The development infrastructure currently includes:

- PostgreSQL 18

PostgreSQL is defined in:

```text
services/postgres.yml
```

## Docker Compose

The main `compose.yml` defines the Compose project and includes the individual service files:

```yaml
name: cinelis_dev

include:
    - services/postgres.yml
```

Additional infrastructure services should be added as separate files under `services/` and included from `compose.yml`.

For example:

```text
services/
├── postgres.yml
├── redis.yml
└── storage.yml
```

## Environment

Infrastructure configuration is read from the environment.

The PostgreSQL service expects:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=cinelis
DB_USER=postgres
DB_PASSWORD=postgres
```

Required Compose variables use the `${VAR:?message}` syntax, so Docker Compose will fail immediately if they are missing.

For example:

```yaml
POSTGRES_DB: ${DB_NAME:?Set DB_NAME}
```

## PostgreSQL

The development PostgreSQL container is named:

```text
cinelis-dev-postgres
```

and uses:

```text
postgres:18-alpine
```

The service is exposed only on the local machine:

```yaml
ports:
    - "127.0.0.1:${DB_PORT:?Set DB_PORT}:5432"
```

Because the PHP applications run directly on the host, they connect to PostgreSQL through:

```text
127.0.0.1:${DB_PORT}
```

The internal PostgreSQL container port remains:

```text
5432
```

## Persistence

PostgreSQL data is stored in the named Docker volume:

```text
postgres-data
```

The service mounts it at:

```text
/var/lib/postgresql
```

This means database data survives normal container recreation.

Running:

```bash
docker compose down
```

does not remove the volume.

Commands such as:

```bash
docker compose down -v
```

or:

```bash
docker volume prune
```

may permanently delete the local development database.

## Healthcheck

PostgreSQL uses `pg_isready` to report when it is ready to accept connections:

```yaml
healthcheck:
    test: ["CMD-SHELL", "pg_isready -U ${DB_USER} -d ${DB_NAME}"]
    interval: 5s
    timeout: 5s
    retries: 5
```

Other services that depend on PostgreSQL can use this health state when startup ordering becomes necessary.

## Commands

Infrastructure commands can be run from the repository root:

```bash
make infra/up
make infra/down
make infra/restart
make infra/logs
make infra/ps
```

Or directly from this directory:

```bash
make up
make down
make restart
make logs
make ps
```

## Start infrastructure

```bash
make up
```

This starts the configured infrastructure services in the background.

## Stop infrastructure

```bash
make down
```

This stops and removes the Compose containers and network while preserving named volumes.

## Restart infrastructure

```bash
make restart
```

## View logs

```bash
make logs
```

## View status

```bash
make ps
```

## Database migrations

Docker is responsible for running PostgreSQL, but schema changes are managed by the Yii console application.

From the repository root:

```bash
make migrate
```

Create a new migration with:

```bash
make migrate/create NAME=create_example_table
```

Migration files live in:

```text
apps/console/migrations/
```

## Adding a new infrastructure service

Create a new file under:

```text
infra/services/
```

For example:

```text
infra/services/redis.yml
```

Then include it from `compose.yml`:

```yaml
name: cinelis_dev

include:
    - services/postgres.yml
    - services/redis.yml
```

Keep each service definition self-contained where possible.

When adding a service:

1. Add its Compose definition under `services/`.
2. Add any required environment variables to `.env.example`.
3. Add persistent volumes where needed.
4. Bind host ports to `127.0.0.1` unless external network access is intentionally required.
5. Add an appropriate healthcheck where useful.
6. Update this README.
