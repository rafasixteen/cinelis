FROM php:8.4-fpm-alpine

ARG APP_DIRECTORY
ARG COMPOSER_DEV_DEPENDENCIES=false

RUN apk add --no-cache libpq-dev \
	&& apk add --no-cache --virtual .build-dependencies $PHPIZE_DEPS \
	&& docker-php-ext-install pdo_pgsql \
	&& apk del .build-dependencies

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/cinelis

COPY composer.json composer.lock ./

RUN if [ "$COMPOSER_DEV_DEPENDENCIES" = "true" ]; then \
		composer install \
			--prefer-dist \
			--no-interaction \
			--no-progress; \
	else \
		composer install \
			--no-dev \
			--prefer-dist \
			--no-interaction \
			--no-progress; \
	fi

COPY common common
COPY assets assets
COPY ${APP_DIRECTORY} ${APP_DIRECTORY}

RUN mkdir -p \
	${APP_DIRECTORY}/runtime \
	${APP_DIRECTORY}/web/assets \
	&& chown -R www-data:www-data /var/www/cinelis

CMD ["php-fpm", "-F"]
