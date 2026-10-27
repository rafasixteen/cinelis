# Cinelis Infrastructure

The API, frontoffice, backoffice, PostgreSQL, and Caddy run through Docker
Compose. Development and production use the same PHP-FPM architecture, but
their filesystem mounts, images, ports, and Caddy configuration are kept
separate.

## Structure

```text
infra/
├── compose.yml              # Shared service topology
├── dev/
│   ├── compose.yml          # Development overrides
│   └── Caddyfile            # Local HTTP proxy
├── prod/
│   ├── compose.yml          # Production overrides
│   └── Caddyfile            # HTTPS proxy
├── docker/
│   ├── caddy/
│   │   └── Dockerfile
│   └── php-fpm/
│       └── Dockerfile
├── Taskfile.yml
└── README.md
```

`compose.yml` contains the shared services, internal network behavior, health
checks, PHP-FPM configuration, and named volumes. The environment-specific
Compose files are applied on top of it.

## Development

Start the development stack from the repository root:

```bash
task infra:up
```

This is equivalent to:

```bash
docker compose \
    --env-file .env \
    -f infra/compose.yml \
    -f infra/dev/compose.yml \
    up -d --build
```

Development adds:

- Read-only bind mounts for `common/` and each application directory.
- Composer development dependencies.
- A fixed local PostgreSQL mapping on `127.0.0.1:5432` for external tools.
- Caddy access on `127.0.0.1:${CADDY_HTTP_PORT}`.
- The local HTTP-only `dev/Caddyfile`.
- `YII_DEBUG=1` and `YII_ENV=dev`.

Source changes are visible immediately because only the application source is
mounted. Runtime data and Yii-published assets remain in named volumes.

The local routes are:

```text
http://cinelis.localhost/       # frontoffice
http://cinelis.localhost/admin/ # backoffice
http://cinelis.localhost/api/   # API
```

Database configuration separates host access from the Docker network:
`DB_HOST` and `DB_PORT` describe host-side access, while
`DB_CONTAINER_HOST` and `DB_CONTAINER_PORT` are injected into the PHP
containers and used by PostgreSQL. The fixed host mapping is only a local
convenience and is not used by the PHP applications.

## Production

Production uses the same base topology with the production overrides:

```bash
docker compose \
    --env-file .env.production \
    -f infra/compose.yml \
    -f infra/prod/compose.yml \
    up -d --build
```

Production adds:

- Immutable application images without source bind mounts.
- Composer production dependencies only.
- A Caddy image containing the public `web/` directories.
- Caddy certificate and configuration volumes.
- Public ports `80` and `443` only on Caddy.
- No published PostgreSQL port.
- The HTTPS `prod/Caddyfile`.
- `YII_DEBUG=0` and `YII_ENV=prod`.

Set `CADDY_DOMAIN` to the real domain and provide the database credentials and
cookie keys through the production environment or a secret manager. The
production PHP-FPM services are reachable only on the internal Compose
network.

## Routing

Both Caddy configurations use the same path-based routing:

```text
/api/*   -> api:9000
/admin/* -> backoffice:9000
/*       -> frontoffice:9000
```

The PHP-FPM services listen on their internal FastCGI port `9000`. They do not
publish host ports. Caddy is the only HTTP entrypoint.

## Volumes

PostgreSQL data is stored in `postgres-data`. Each Yii application has separate
runtime and published-asset volumes. Caddy mounts the asset volumes read-only.

Normal `docker compose down` preserves these volumes. Use `down -v` only when
intentionally deleting local database and generated application data.

## Commands

```bash
task infra:up
task infra:down
task infra:restart
task infra:logs
task infra:ps
```

Database migrations run through the host-side Yii console application. The
console is not a Compose service:

```bash
task migrate
```

The root `task dev` command starts this development stack and the Tailwind
watchers through dekit.
