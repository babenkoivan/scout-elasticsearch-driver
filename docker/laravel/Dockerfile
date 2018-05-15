ARG version

FROM composer:latest

WORKDIR /app/laravel

# Install Laravel
RUN composer create-project --no-interaction --prefer-dist laravel/laravel . $version

# Setup environment
COPY ./docker/laravel/.env .env

# Copy migrations
RUN rm database/migrations/*
COPY ./docker/laravel/database/migrations/* database/migrations/

# Copy seeders
RUN rm database/seeds/*
COPY ./docker/laravel/database/seeds/* database/seeds/

# Regenerate autoloader
RUN composer dump-autoload

# Bootstrap
COPY ./docker/laravel/bootstrap.sh /usr/local/bin/bootstrap
RUN chmod 544 /usr/local/bin/bootstrap
CMD ["bootstrap"]
