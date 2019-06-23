FROM composer:latest

WORKDIR /app/laravel

# Install Laravel
ARG version
RUN composer create-project --no-interaction --prefer-dist laravel/laravel . $version

# Setup environment
COPY .env .env

# Copy migrations
RUN rm database/migrations/*
COPY database/migrations/* database/migrations/

# Copy seeders
RUN rm database/seeds/*
COPY database/seeds/* database/seeds/

# Regenerate autoloader
RUN composer dump-autoload --no-interaction
