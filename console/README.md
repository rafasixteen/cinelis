# Console

The console application owns Yii database migrations and RBAC administration.
In development and production it runs as a short-lived container from the
environment-specific Compose file; it is not a continuously running service.
The RBAC migration creates the Yii tables, while `rbac/init` owns the
application roles, permissions, and hierarchy.

## Development

From the repository root:

```bash
task migrate
task migrate:status
task migrate:create -- create_example_table
```

These commands run the `console` service from `infra/dev/compose.yml`.
Migration files are written back to the repository through the development
console bind mount.

## Production

The deployment script runs the production console automatically:

```bash
docker compose \
    --env-file .env \
    -f infra/prod/compose.yml \
    run --rm --no-deps console php yii migrate --interactive=0
```

RBAC initialization is then run with:

```bash
docker compose \
    --env-file .env \
    -f infra/prod/compose.yml \
    run --rm --no-deps console php yii rbac/init
```

Production database backups are created before migrations by
`scripts/deploy.sh`.
