ARG version

FROM php:${version}-cli

WORKDIR /app/laravel

# Install necessary php extensions
RUN docker-php-ext-install \
    pdo_mysql

# Todo run migrations

ENTRYPOINT ["php", "artisan"]
