# API

The API exposes Cinelis functionality to external clients, primarily the Android application.

The API is responsible for HTTP concerns such as:

- routing
- request validation
- authentication and authorization
- serialization
- HTTP status codes

Business logic should not be duplicated in API controllers.

Shared application and domain logic belongs in:

```text
common/
```

## Structure

```text
api/
├── config/
├── controllers/
├── models/
├── runtime/
├── web/
└── Makefile
```

The public web root is:

```text
web/
```

The application entrypoint is:

```text
web/index.php
```

## Development

From the repository root:

```bash
make api/dev
```

Or from this directory:

```bash
make dev
```
