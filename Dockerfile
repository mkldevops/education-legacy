#syntax=docker/dockerfile:1.7

FROM php:8.2-fpm-alpine as base

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions gd opcache pdo_mysql zip intl @composer

RUN apk add --no-cache $PHPIZE_DEPS git build-base zsh shadow

RUN set -eux; \
	install-php-extensions imagick

RUN set -eux; \
	curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | sh && \
	apk add symfony-cli && \
	sh -c "$(curl -fsSL https://raw.github.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"

ENV APP_ENV=prod

WORKDIR /srv/app

COPY --link docker/app.ini $PHP_INI_DIR/conf.d/

EXPOSE 80
CMD ["symfony", "serve", "--no-tls", "--allow-http", "--port=80"]

FROM base as prod

COPY --link . .
RUN set -eux; \
	symfony composer install --no-cache --prefer-dist --no-scripts --no-progress

FROM base as dev

ENV APP_ENV=dev
