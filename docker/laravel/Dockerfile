ARG version

FROM composer:latest

WORKDIR /app/laravel

# Install Laravel
RUN composer create-project --no-interaction --prefer-dist laravel/laravel . $version

# Todo copy default models, migrations, seeders

# Copy driver package files
COPY . ../scout-elasticsearch-driver

# Add driver repository
RUN composer config --no-interaction repositories.scout-elasticsearch-driver \
    '{"type": "package", "package": {"name": "babenkoivan/scout-elasticsearch-driver-local", \
    "version": "dev", "dist": {"type": "path", "url": "../scout-elasticsearch-driver", \
    "options": {"symlink": true}}}}'

# Require driver package
RUN composer require --no-interaction babenkoivan/scout-elasticsearch-driver-local:@dev
