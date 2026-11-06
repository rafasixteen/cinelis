#!/bin/sh

set -eu

if [ "$#" -eq 0 ]; then
	printf '%s\n' 'cinelis-entrypoint requires a command.' >&2
	exit 64
fi

if [ -n "${CINELIS_APP_ORIGIN:-}" ]; then
	printf '[%s] %s\n' "${CINELIS_APP_LABEL:-Cinelis}" "$CINELIS_APP_ORIGIN"
fi

if [ "${CINELIS_PREPARE_GII_PERMISSIONS:-0}" = '1' ]; then
	for directory in /var/www/cinelis/common /var/www/cinelis/common/models; do
		if ! mkdir -p "$directory" 2>/dev/null || ! chmod 0777 "$directory" 2>/dev/null; then
			printf 'Warning: could not make %s writable for Gii.\n' "$directory" >&2
		fi

		printf 'make %s writable for Gii.\n' "$directory" >&2
	done
fi

exec "$@"
