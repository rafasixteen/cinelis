# API

The API exposes Cinelis functionality to external clients.

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
└── Taskfile.yml
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

From the repository root, start the shared Docker infrastructure and all
development processes:

```bash
task dev
```

The API is available through the Caddy path prefix:

```text
http://cinelis.localhost/api/
```

Use the dekit TUI to inspect the API PHP-FPM logs or run `dekit screen api` to
print its current output.
