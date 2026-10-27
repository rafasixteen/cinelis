# Backoffice

The backoffice is the internal Cinelis management application.

It is intended for cinema staff and administrators.

Responsibilities include:

- managing movies
- managing cinema rooms
- managing seats
- managing sessions
- viewing and managing orders
- managing users and administrative data

## Structure

```text
backoffice/
├── assets/
│   └── css/
├── config/
├── controllers/
├── models/
├── runtime/
├── views/
│   ├── components/
│   ├── layouts/
│   └── ...
├── web/
└── Taskfile.yml
```

Controllers and views should contain backoffice-specific presentation logic.

Shared business rules and persistence logic should live in:

```text
common/
```

## Development

From the repository root, start the backoffice together with the other applications:

```bash
task dev
```
