FROM php:8.0-apache

RUN usermod -u 48 www-data && groupmod -g 48 www-data
RUN mkdir -p -m 777 /opt/apache/sessiontmp5/

RUN apt update && apt install -y zip curl vim mycli --no-install-recommends
RUN apt install -y libmagickwand-dev libzip-dev git  --no-install-recommends

# Install ZSH
RUN apt install -y zsh
RUN sh -c "$(curl -fsSL https://raw.github.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"

RUN docker-php-ext-install -j$(nproc) opcache pdo_mysql zip intl

RUN mkdir -p /usr/src/php/ext/imagick; \
    curl -fsSL https://github.com/Imagick/imagick/archive/06116aa24b76edaf6b1693198f79e6c295eda8a9.tar.gz | tar xvz -C "/usr/src/php/ext/imagick" --strip 1; \
    docker-php-ext-install imagick;

EXPOSE 80

# CRON
RUN apt install -y cron
COPY docker/cron /etc/cron.d/cron
RUN chmod 0644 /etc/cron.d/cron
RUN crontab /etc/cron.d/cron
RUN touch /var/log/cron.log
RUN sed -i 's/^exec /service cron start\n\nexec /' /usr/local/bin/apache2-foreground

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html/
COPY ./ /var/www/html

COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache.conf /etc/apache2/conf-available/z-app.conf

RUN make install

RUN a2enmod rewrite remoteip && \
    a2enconf z-app

# Clean APT
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*