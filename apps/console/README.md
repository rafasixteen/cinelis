# Console

The console application contains Cinelis command-line functionality.

It does not run an HTTP server.

It is used for:

- database migrations
- database seeders
- maintenance tasks
- imports and exports
- scheduled commands
- one-off administrative operations

## Structure

```text
console/
├── config/
├── controllers/
├── migrations/
└── runtime/
```

## Database migrations

Create a migration from the repository root:

```bash
make migrate/create NAME=create_movies_table
```

Apply migrations:

```bash
make migrate
```

Migration files belong in:

```text
apps/console/migrations/
```

There should be one migration history for the Cinelis database rather than separate migration sets for each application.
