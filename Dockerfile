#syntax=docker/dockerfile:1.4

FROM php:8.2-apache as php-base

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data \
    && mkdir -p -m 777 /opt/apache/sessiontmp5/

RUN apt update && apt install -y zip curl vim mycli git zsh --no-install-recommends && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN install-php-extensions gd opcache pdo_mysql zip intl imagick @composer

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash && \
    apt install symfony-cli && \
    sh -c "$(curl -fsSL https://raw.github.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"

COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

FROM registry.gitlab.com/msadawaheri-projects/education/php:8.2-base as app

WORKDIR /var/www/html/
COPY --link . .

COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache.conf /etc/apache2/conf-available/z-app.conf

RUN a2enmod rewrite remoteip && \
    a2enconf z-app