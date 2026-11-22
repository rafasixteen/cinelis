FROM node:24-alpine AS assets

WORKDIR /build

RUN npm install --no-save --no-package-lock --ignore-scripts \
	"@tailwindcss/cli@4.3.3" "tailwindcss@4.3.3"

COPY common common
COPY backoffice backoffice
COPY frontoffice frontoffice

RUN ./node_modules/.bin/tailwindcss \
		-i backoffice/assets/css/globals.css \
		-o backoffice/web/css/globals.css \
	&& ./node_modules/.bin/tailwindcss \
		-i frontoffice/assets/css/globals.css \
		-o frontoffice/web/css/globals.css

FROM caddy:2-alpine

COPY api/web /var/www/cinelis/api/web
COPY --from=assets /build/backoffice/web /var/www/cinelis/backoffice/web
COPY --from=assets /build/frontoffice/web /var/www/cinelis/frontoffice/web
