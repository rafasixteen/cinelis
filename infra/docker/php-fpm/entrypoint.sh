#!/bin/sh

set -eu

if [ "$#" -eq 0 ]; then
	printf '%s\n' 'cinelis-entrypoint requires a command.' >&2
	exit 64
fi

if [ -n "${CINELIS_APP_ORIGIN:-}" ]; then
	printf '[%s] %s\n' "${CINELIS_APP_LABEL:-Cinelis}" "$CINELIS_APP_ORIGIN"
fi

exec "$@"
