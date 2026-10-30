# Cinelis

Cinelis is a cinema management system built from several PHP applications,
shared domain code, a native Android prototype, and Docker-based infrastructure.

## Repository

- [`api`](api/README.md) — HTTP API
- [`backoffice`](backoffice/README.md) — internal administration
- [`frontoffice`](frontoffice/README.md) — public cinema website
- [`console`](console/README.md) — Yii migrations and CLI commands
- [`android`](android/README.md) — Android UI prototype
- [`common`](common/README.md) — shared PHP code
- [`infra`](infra/README.md) — Docker Compose, PostgreSQL, and Caddy
- `docs/` — local API documentation assets

PHP applications run in Docker containers. Source changes are mounted for
development; Caddy is the local HTTP entrypoint.

## Requirements

- [mise](https://mise.jdx.dev/)
- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- Git

### Windows

Install the prerequisites from PowerShell:

```powershell
winget install --id jdx.mise --exact
winget install --id Docker.DockerDesktop --exact
winget install --id Git.Git --exact
```

Open a new PowerShell window and run this once to activate mise in the current
and future terminals:

```powershell
if (-not (Test-Path $PROFILE)) { New-Item -ItemType File -Path $PROFILE -Force | Out-Null }; if (-not (Select-String -LiteralPath $PROFILE -Pattern 'mise activate pwsh' -Quiet -ErrorAction SilentlyContinue)) { Add-Content -LiteralPath $PROFILE -Value '$env:MISE_PWSH_CHPWD_WARNING = "0"; (& mise activate pwsh) | Out-String | Invoke-Expression' }; . $PROFILE
```

## Setup

From the repository root:

```sh
mise install
composer install
```

Create `.env` from the example:

```sh
cp .env.example .env
```

On Windows PowerShell, use `Copy-Item .env.example .env` instead.

Verify the toolchain:

```powershell
mise doctor
task --version
dekit --version
php --version
composer --version
```

## Development

Start the full stack:

```powershell
task dev
```

Common commands:

- Reattach to the dekit TUI:

    ```powershell
    task dev:attach
    ```

- Stop the TUI and infrastructure:

    ```powershell
    task dev:stop
    ```

- Start or inspect infrastructure:

    ```powershell
    task infra:up
    task infra:ps
    task infra:logs
    task infra:down
    ```

- Run migrations:

    ```powershell
    task migrate
    task migrate:create -- name
    ```

Local URLs:

- <http://cinelis.localhost/> — frontoffice
- <http://cinelis.localhost/admin/> — backoffice
- <http://cinelis.localhost/api/> — API

## Conventions

Use Yii2 conventions and keep shared business logic under `common/`. Keep
application-specific HTTP code in its application directory. Do not edit
generated assets under `web/`.

Before committing, run the relevant checks and make sure the stack starts:

```powershell
task format:check
task infra:up
task migrate
```

Do not commit `.env`, `vendor/`, runtime files, generated assets, or local IDE
configuration.
