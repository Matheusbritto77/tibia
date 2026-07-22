FROM php:8.3-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
		ca-certificates \
		curl \
		git \
		libicu-dev \
		libpng-dev \
		libzip-dev \
		libonig-dev \
		libxml2-dev \
		unzip \
	&& docker-php-ext-install -j"$(nproc)" gd intl mysqli opcache pdo_mysql zip bcmath mbstring xml \
	&& a2enmod headers rewrite \
	&& rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN set -eux; \
	git clone --depth 1 https://github.com/opentibiabr/canaryaac.git /var/www/html; \
	cd /var/www/html; \
	composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader; \
	chown -R www-data:www-data /var/www/html

COPY --chmod=755 quickstart/canaryaac/entrypoint.sh /usr/local/bin/canaryaac-entrypoint
COPY quickstart/canaryaac/bootstrap.php /usr/local/bin/canaryaac-bootstrap.php

ENTRYPOINT ["canaryaac-entrypoint"]
CMD ["apache2-foreground"]
