FROM php:7.3.33-cli-alpine

MAINTAINER Zhao Zhang <zo.zhang@gmail.com>

LABEL Description="This is a multi-dimensional JSON data conversion based on Symfony, using ParseCsv class to export csv file."
LABEL Version="1.0"

# Install Git
RUN apk add --no-cache git

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --version=2.0.13 --install-dir=/usr/local/bin --filename=composer

# Copy application code source
RUN set -ex; \
	curl --output code.tar.xz --location https://github.com/ZoZhang/format-json-test/archive/v1.0.tar.gz; \
    tar -xf code.tar.xz -C /var/www/html; \
    chown -R www-data:www-data /var/www/html; \
    rm -rf code.tar.xz;

USER www-data

# Install dependency
RUN set -ex; \
    cd /var/www/html/format-json-test-1.0; \
    composer install;