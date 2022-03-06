FROM php:8.1-apache

RUN usermod -u 48 www-data && groupmod -g 48 www-data
RUN mkdir -p -m 777 /opt/apache/sessiontmp5/

RUN apt update && apt install -y zip curl vim mycli git zsh --no-install-recommends
RUN apt install -y zlib1g-dev libmagickwand-dev libzip-dev --no-install-recommends

RUN apt-get update && apt-get install -y libpng-dev
RUN apt-get install -y \
    libwebp-dev \
    libjpeg62-turbo-dev \
    libpng-dev libxpm-dev \
    libfreetype6-dev

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions gd opcache pdo_mysql zip intl imagick @composer

# Install ZSH
RUN sh -c "$(curl -fsSL https://raw.github.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"

EXPOSE 80

WORKDIR /var/www/html/
COPY ./ /var/www/html

COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache.conf /etc/apache2/conf-available/z-app.conf

#RUN make install

RUN a2enmod rewrite remoteip && \
    a2enconf z-app

# Clean APT
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*