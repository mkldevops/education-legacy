FROM php:8.1-apache as php

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data
RUN mkdir -p -m 777 /opt/apache/sessiontmp5/

RUN apt update && apt install -y zip curl vim mycli git zsh --no-install-recommends

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions gd opcache pdo_mysql zip intl imagick @composer

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
RUN apt install symfony-cli

RUN symfony -V
# Install ZSH
RUN sh -c "$(curl -fsSL https://raw.github.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"

COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

FROM php_base as app

EXPOSE 80

WORKDIR /var/www/html/
COPY ./ /var/www/html

COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache.conf /etc/apache2/conf-available/z-app.conf

#RUN make install

RUN a2enmod rewrite remoteip && \
    a2enconf z-app

# Clean APT
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*