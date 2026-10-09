# Frontoffice

The frontoffice is the public-facing Cinelis web application.

It is responsible for functionality used by cinema customers, including:

- browsing movies
- viewing sessions
- selecting screenings
- seat selection
- checkout
- customer accounts

## Structure

```text
frontoffice/
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
├── dekit.yaml
└── Makefile
```

### Controllers

Controllers belong in:

```text
controllers/
```

They should contain HTTP and presentation orchestration only.

Business logic shared with other applications belongs in `common`.

### Views

Views are grouped by controller:

```text
views/home/index.php
views/movie/index.php
views/movie/view.php
```

Shared partials belong in:

```text
views/components/
```

Application layouts belong in:

```text
views/layouts/
```

### CSS

Source CSS belongs in:

```text
assets/css/
```

The main stylesheet is:

```text
assets/css/globals.css
```

Reusable component styles belong in:

```text
assets/css/components/
```

Page-specific styles belong in:

```text
assets/css/views/
```

Tailwind compiles the source CSS into:

```text
web/css/globals.css
```

Do not manually edit the generated file.

## Development

From the repository root:

```bash
make frontoffice/dev
```

Or from this directory:

```bash
make dev
```

Dekit runs the development processes defined in `dekit.yaml`, including the PHP server and Tailwind watcher.
