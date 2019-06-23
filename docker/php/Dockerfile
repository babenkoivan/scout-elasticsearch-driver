ARG version

FROM php:${version}-cli

# Install system libraries
RUN apt-get update -y && apt-get install -y \
    unzip \
    zlib1g-dev \
    git \
    netcat

# Install php extensions
RUN docker-php-ext-install \
    zip \
    pdo_mysql

# Install composer
RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer

WORKDIR /app/laravel

# Bootstrap
COPY bootstrap /usr/local/bin/bootstrap
RUN chmod 544 /usr/local/bin/bootstrap
CMD ["bootstrap"]
