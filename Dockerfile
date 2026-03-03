FROM php:8.3-fpm-alpine


RUN apk update && apk add --update nodejs npm \
    composer php-pdo_sqlite php-pdo_mysql php-pdo_pgsql php-simplexml php-fileinfo php-dom php-tokenizer php-xml php-xmlwriter php-session \
    openrc bash nginx

RUN docker-php-ext-install pdo

RUN docker-php-ext-install pdo_mysql

COPY --chown=www-data:www-data . /app
WORKDIR /app

# Overwrite default nginx config
COPY nginx.conf /etc/nginx/nginx.conf

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN composer install
RUN  composer dump-autoload
RUN touch /app/storage/db.sqlite
RUN chown www-data:www-data /app/storage/db.sqlite

# RUN composer build

ENTRYPOINT [ "sh", "/app/entrypoint.sh" ]
