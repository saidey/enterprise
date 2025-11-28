FROM php:8.2-fpm-alpine

# Install system deps
RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    oniguruma-dev \
    libzip-dev \
    postgresql-dev \
    mysql-client

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring zip
RUN docker-php-ext-install pcntl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www
COPY . /var/www

# Supervisor
RUN apk add --no-cache supervisor \
    && mkdir -p /etc/supervisor/conf.d /var/log/supervisor

COPY supervisord.conf /etc/supervisord.conf
COPY supervisor-queue.conf /etc/supervisor/conf.d/queue.conf
COPY supervisor-php-fpm.conf /etc/supervisor/conf.d/php-fpm.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]