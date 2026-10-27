# Cinelis

Cinelis is a cinema management system composed of several applications sharing the same domain and infrastructure.

## Project structure

```text
cine-lis/
├── api/
├── backoffice/
├── console/
├── frontoffice/
├── docs/
├── common/
├── infra/
├── composer.json
├── composer.lock
├── mise.toml
├── Taskfile.yml
└── yii
```

### Applications

- [`api`](api/README.md) — HTTP API used by external clients.
- [`backoffice`](backoffice/README.md) — internal web application for cinema management.
- [`frontoffice`](frontoffice/README.md) — public-facing cinema website.
- [`console`](console/README.md) — Yii console application for migrations, maintenance tasks, seeders, and CLI commands.

### Shared code

[`common`](common/README.md) contains PHP code shared between the PHP applications.

This includes:

- application configuration
- domain models
- application services
- repositories
- infrastructure integrations
- shared utilities

Application-specific HTTP concerns, views, forms, and presentation logic should remain inside the corresponding app.

### Infrastructure

[`infra`](infra/README.md) contains the local infrastructure required by the applications, such as PostgreSQL.

Infrastructure and PHP applications run through Docker Compose during
development. Each PHP-FPM container contains only its application code and
the shared `common/` code, with those source directories mounted read-only for
live changes. Caddy provides the single local entrypoint.

See [`infra/README.md`](infra/README.md) for details.

---

## Requirements

Project tooling is managed with [mise](https://mise.jdx.dev/).

Install mise, then from the repository root run:

```bash
mise install
```

This installs the required project tools and versions.

The project currently uses:

- PHP
- Composer
- Node.js
- Tailwind CSS CLI
- Task
- dekit
- Docker

Docker itself must be installed separately.

---

## Environment

Create the local environment file:

```bash
php -r "copy('.env.example', '.env');"
```

The `.env` file contains local configuration such as:

```dotenv
APP_ENV=development

CADDY_DOMAIN=cinelis.localhost
CADDY_HTTP_PORT=80
CADDY_API_UPSTREAM=api:9000
CADDY_BACKOFFICE_UPSTREAM=backoffice:9000
CADDY_FRONTOFFICE_UPSTREAM=frontoffice:9000

DB_HOST=localhost
DB_PORT=5432
DB_CONTAINER_HOST=postgres
DB_CONTAINER_PORT=5432
DB_NAME=cinelis
DB_USER=postgres
DB_PASSWORD=postgres
```

Secrets and developer-specific values belong in `.env`.

Do not commit `.env`.

---

## Install dependencies

PHP dependencies are installed inside the Docker images during
`task infra:up`. The host-side console commands also require the repository
Composer dependencies:

```bash
composer install
```

---

## Start infrastructure

```bash
task infra:up
```

Check its status:

```bash
task infra:ps
```

Follow logs:

```bash
task infra:logs
```

Stop it:

```bash
task infra:down
```

---

## Database migrations

Create a migration:

```bash
task migrate:create -- create_movies_table
```

Apply pending migrations:

```bash
task migrate
```

Migrations live in:

```text
console/migrations/
```

The PHP applications share the same PostgreSQL database.

---

## Running applications

Run the API, backoffice, and frontoffice together:

```bash
task dev
```

This opens the dekit TUI with separate, labeled processes for the API,
frontoffice, and backoffice. Use the TUI to inspect output and start, stop, or
restart individual processes. Reattach with:

```bash
task dev:attach
```

Stop all development processes with:

```bash
task dev:stop
```

With infrastructure running, use the local proxy at:

```text
http://cinelis.localhost/       # frontoffice
http://cinelis.localhost/admin/ # backoffice
http://cinelis.localhost/api/   # API
```

The proxy runs in the Caddy container and is the only HTTP entrypoint. The PHP
applications are reachable only through the internal Compose network.

Restart an individual process from the command line, for example:

```bash
dekit restart api
```

You can see all available commands with:

```bash
task --list
```

## Serve documentation locally

```bash
task docs:serve
```

---

## Development conventions

### PHP applications

The PHP applications use Yii2.

Keep controllers focused on HTTP concerns:

```text
request
  ↓
controller
  ↓
application/domain logic
  ↓
repository
  ↓
database
```

Do not place shared business logic directly in frontoffice, backoffice, or API controllers.

Shared business logic belongs under:

```text
common/
```

### Views

Yii views use the conventional structure:

```text
views/
├── components/
├── layouts/
└── <controller>/
```

For example:

```text
frontoffice/views/
├── components/
│   ├── header.php
│   └── footer.php
├── layouts/
│   └── main.php
└── home/
    └── index.php
```

### CSS

Source CSS lives under:

```text
assets/css/
```

Generated browser assets live under:

```text
web/css/
```

Do not manually edit generated CSS files.

CSS is organized into:

```text
assets/css/
├── globals.css
├── components/
└── views/
```

Use:

- `@layer base` for global element styles and theme variables
- `@layer components` for application components and view styles
- raw CSS for layout and detailed styling
- `@apply` primarily for semantic theme utilities such as colors and borders

Example:

```css
@layer components {
	.movie-card {
		@apply border border-border bg-card;

		display: grid;
		gap: 1rem;
	}
}
```

### Naming

PHP classes use PascalCase:

```text
HomeController.php
MovieRepository.php
CreateOrder.php
```

Views and partials use lowercase or kebab-case:

```text
index.php
header.php
movie-card.php
```

CSS classes may be locally scoped using nesting:

```css
.home {
	.hero {
		.title {
			...
		}
	}
}
```

---

## Before committing

Make sure:

```bash
task infra:up
task migrate
```

work on your branch and that the applications you modified start successfully.

Do not commit:

- `.env`
- `vendor/`
- runtime files
- generated Yii assets
- generated Tailwind CSS
- local IDE configuration
