# Cinelis Infrastructure

Development and production are separate, self-contained Compose stacks:

```text
infra/
├── dev/
│   ├── compose.yml       # Local development services
│   └── Caddyfile         # Local HTTP routing
├── prod/
│   ├── compose.yml       # Production services
│   └── Caddyfile         # HTTPS routing
├── docker/
│   ├── caddy/Dockerfile
│   └── php-fpm/
│       ├── Dockerfile
│       └── entrypoint.sh
└── Taskfile.yml
```

Neither stack extends or overrides another Compose file. Each Compose file
defines its own services, volumes, builds, environment, and dependencies.

## Development

From the repository root:

```bash
task infra:up
```

This runs:

```bash
docker compose \
    --env-file .env \
    -f infra/dev/compose.yml \
    up -d --build
```

The development stack provides PostgreSQL on `127.0.0.1`, source bind mounts,
Composer development dependencies, Gii support, and local HTTP through Caddy.

Use the infrastructure tasks for status, logs, restart, and shutdown. The
restart task also recreates the generated frontoffice asset volume so changes
to JavaScript, CSS, and fonts are republished:

```bash
task infra:ps
task infra:logs
task infra:restart
task infra:down
```

Database operations run inside the development console container:

```bash
task migrate
task migrate:status
task migrate:create -- name
```

## Production

Create a production environment file from the template:

```bash
cp .env.example .env
chmod 600 .env
```

Set a real domain, strong database credentials, and long random cookie keys.
Keep this file out of Git.

The production stack contains PostgreSQL on a private Compose network, the
three PHP-FPM applications, a one-off console image for migrations and RBAC,
and Caddy as the only public HTTP entrypoint. PostgreSQL and Caddy data use
persistent named volumes, with `YII_DEBUG=0` and `YII_ENV=prod`.

Deploy from the repository checkout with:

```bash
./scripts/deploy.sh
```

The deployment script uses `infra/prod/compose.yml`, updates the `main`
checkout, validates Compose, builds images, starts PostgreSQL, creates a
custom-format database backup, runs migrations, initializes RBAC, starts the
application services, and checks the API health endpoint.

The same command can be run remotely:

```bash
ssh grupo-d-5@your-server \
    'cd /opt/cinelis && ./scripts/deploy.sh'
```

The server needs Git, Docker Engine, Docker Compose, and `curl`. It does not
need host-installed PHP, Composer, PostgreSQL, Caddy, or Nginx.

## Rollback

Rollbacks are Git operations. Revert the bad change on `main`, push it, and run
the normal deployment command again:

```bash
git revert <commit>
git push origin main
ssh grupo-d-5@your-server \
    'cd /opt/cinelis && ./scripts/deploy.sh'
```

The deployment script never runs `docker compose down -v`; named volumes are
preserved between deployments.
