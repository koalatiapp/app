# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
ARG PHP_VERSION=8.1
ARG CADDY_VERSION=2

# "php" stage
FROM php:${PHP_VERSION}-fpm-alpine AS symfony_php

# persistent / runtime deps
RUN apk add --no-cache \
		acl \
		fcgi \
		file \
		gettext \
		git \
		jq \
	;

ARG APCU_VERSION=5.1.19
RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
		$PHPIZE_DEPS \
		icu-dev \
		libzip-dev \
		libxslt-dev \
		zlib-dev \
	; \
	apk add --update nodejs npm supervisor; \
	docker-php-ext-configure zip; \
	docker-php-ext-install -j$(nproc) \
		pcntl \
		intl \
		zip \
		mysqli \
		pdo \
		pdo_mysql \
		xsl \
		bcmath \
	; \
	pecl install \
		apcu-${APCU_VERSION} \
		redis \
	; \
	pecl clear-cache; \
	docker-php-ext-enable \
		apcu \
		redis \
		opcache \
	; \
	\
	runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --no-cache --virtual .phpexts-rundeps $runDeps; \
	\
	apk del .build-deps

COPY docker/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
RUN chmod +x /usr/local/bin/docker-healthcheck

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["docker-healthcheck"]

RUN ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
COPY docker/php/conf.d/symfony.prod.ini $PHP_INI_DIR/conf.d/symfony.ini

COPY docker/php/supervisor/supervisord.conf /etc/supervisord.conf

COPY docker/php/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

VOLUME /var/run/php

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /srv/app

# Allow to use development versions of Symfony
ARG STABILITY="stable"
ENV STABILITY ${STABILITY:-stable}

# Allow to select skeleton version
ARG SYMFONY_VERSION="5.4.*"

# Download the Symfony skeleton and leverage Docker cache layers
RUN composer create-project "symfony/skeleton ${SYMFONY_VERSION}" . --stability=$STABILITY --prefer-dist --no-dev --no-progress --no-interaction; \
	composer clear-cache

###> recipes ###
###< recipes ###

COPY . .

ARG APP_ENV="prod"
ENV APP_ENV ${APP_ENV:-prod}
RUN set -eux; \
	mkdir -p var/cache var/log; \
	if [ "$APP_ENV" = "prod" ]; then \
		composer install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction; \
		composer dump-autoload --classmap-authoritative --no-dev; \
		composer symfony:dump-env prod; \
		composer run-script --no-dev post-install-cmd; \
	else \
		composer install --prefer-dist --no-progress --no-scripts --no-interaction; \
		composer dump-autoload --classmap-authoritative; \
		composer symfony:dump-env dev; \
		composer run-script --dev post-install-cmd; \
	fi; \
	npm ci --production; \
	npm run encore production; \
	chmod +x bin/console; sync
VOLUME /srv/app/var

ENTRYPOINT ["docker-entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

FROM caddy:${CADDY_VERSION}-builder-alpine AS symfony_caddy_builder

RUN xcaddy build \
	--with github.com/dunglas/mercure/caddy \
	--with github.com/dunglas/vulcain/caddy

FROM caddy:${CADDY_VERSION} AS symfony_caddy

WORKDIR /srv/app

COPY --from=dunglas/mercure:v0.11 /srv/public /srv/mercure-assets/
COPY --from=symfony_caddy_builder /usr/bin/caddy /usr/bin/caddy
COPY --from=symfony_php /srv/app/public public/
COPY docker/caddy/Caddyfile /etc/caddy/Caddyfile
