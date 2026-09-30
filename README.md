# Cinelis

Cinelis is a cinema management system composed of several applications sharing the same domain and infrastructure.

## Project structure

```text
cine-lis/
├── apps/
│   ├── api/
│   ├── backoffice/
│   ├── console/
│   ├── frontoffice/
│   └── mobile/
├── packages/
│   └── common/
├── infra/
├── composer.json
├── composer.lock
├── mise.toml
├── Makefile
└── yii
```

### Applications

- [`apps/api`](apps/api/README.md) — HTTP API used by the mobile app and other clients.
- [`apps/backoffice`](apps/backoffice/README.md) — internal web application for cinema management.
- [`apps/frontoffice`](apps/frontoffice/README.md) — public-facing cinema website.
- [`apps/console`](apps/console/README.md) — Yii console application for migrations, maintenance tasks, seeders, and CLI commands.
- [`apps/mobile`](apps/mobile/README.md) — Android application.

### Shared code

[`packages/common`](packages/common/README.md) contains PHP code shared between the PHP applications.

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

Infrastructure runs through Docker while the PHP applications run directly on the host during development.

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
- Dekit
- Make
- Docker

Docker itself must be installed separately.

---

## Environment

Create the local environment file:

```bash
cp .env.example .env
```

The `.env` file contains local configuration such as:

```dotenv
APP_ENV=development

API_PORT=8002
BACKOFFICE_PORT=8001
FRONTOFFICE_PORT=8000

DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=cinelis
DB_USER=postgres
DB_PASSWORD=postgres
```

Secrets and developer-specific values belong in `.env`.

Do not commit `.env`.

---

## Install dependencies

Install PHP dependencies from the repository root:

```bash
composer install
```

After changing Composer namespaces or autoload configuration:

```bash
composer dump-autoload
```

---

## Start infrastructure

```bash
make infra/up
```

Check its status:

```bash
make infra/ps
```

Follow logs:

```bash
make infra/logs
```

Stop it:

```bash
make infra/down
```

---

## Database migrations

Create a migration:

```bash
make migrate/create NAME=create_movies_table
```

Apply pending migrations:

```bash
make migrate
```

Migrations live in:

```text
apps/console/migrations/
```

The PHP applications share the same PostgreSQL database.

---

## Running applications

Each application has its own `Makefile`.

Run the frontoffice:

```bash
make frontoffice/dev
```

Run the backoffice:

```bash
make backoffice/dev
```

Run the API:

```bash
make api/dev
```

You can see the commands available for an application with:

```bash
make frontoffice/help
make backoffice/help
make api/help
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
packages/common/
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
apps/frontoffice/views/
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
composer install
make infra/up
make migrate
```

work on your branch and that the applications you modified start successfully.

Do not commit:

- `.env`
- `vendor/`
- runtime files
- generated Yii assets
- generated Tailwind CSS
- local IDE configuration
