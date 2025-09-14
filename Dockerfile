FROM dunglas/frankenphp:php8.4-alpine AS base

RUN set -eux; \
	apk add --no-cache $PHPIZE_DEPS git build-base zsh shadow;\
	install-php-extensions opcache pdo_mysql zip intl @composer imagick


RUN set -eux; \
	curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | sh && \
	apk add symfony-cli && \
	sh -c "$(curl -fsSL https://raw.github.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"

ENV APP_ENV=prod
ENV SERVER_NAME=:80

WORKDIR /app

COPY --link docker/app.ini $PHP_INI_DIR/conf.d/

FROM base AS prod

COPY --link . .
RUN set -eux; \
	symfony composer install --no-cache --prefer-dist --no-scripts --no-progress

FROM base AS dev

ENV APP_ENV=dev

# Copy source code and configuration
COPY --link . .

# Install dependencies with dev requirements  
RUN set -eux; \
	symfony composer install --no-cache --prefer-dist --no-progress

# Set proper permissions
RUN set -eux; \
	chown -R www-data:www-data /app && \
	chmod +x bin/console

# Warmup cache for better performance (ignore errors in case of missing dependencies)
RUN set -eux; \
	php bin/console cache:warmup --env=dev || true
