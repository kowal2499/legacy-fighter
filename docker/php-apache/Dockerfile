FROM php:8.0-apache

MAINTAINER Roman Kowalski <roman@erla.pl>

RUN a2enmod rewrite expires;

RUN apt-get update; \
    apt-get install -y --no-install-recommends \
    		libfreetype6-dev \
    		libicu-dev \
    		libjpeg-dev \
    		libmagickwand-dev \
    		libpng-dev \
    		libwebp-dev \
    		libzip-dev \
            libpq-dev \
            unzip \
            zip \
            less \
            vim; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install bcmath exif gd intl pdo pdo_pgsql zip; \
    pecl install imagick-3.6.0; \
    docker-php-ext-enable imagick; \
    rm -r /tmp/pear;

# Apache defaults
ENV APACHE_SERVER_ADMIN=roman@erla.pl
COPY ./docker/php-apache/000-default.conf /etc/apache2/sites-enabled/000-default.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# php.ini
COPY ./docker/php-apache/php.ini $PHP_INI_DIR/conf.d/

ARG PUID=1000
ARG PGID=1000

ENV PUID ${PUID}
ENV PGID ${PGID}

RUN groupadd -g ${PGID} user && \
    useradd -u ${PUID} -g user -m user

VOLUME /var/www/html

USER root
# Install composer
ENV GITHUB_TOKEN ${GITHUB_TOKEN}
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.7.0 --config github-oauth.github.com ${GITHUB_TOKEN}

USER user
WORKDIR /var/www/html
