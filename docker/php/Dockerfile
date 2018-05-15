ARG version

FROM php:${version}-cli

# Install necessary php extensions
RUN docker-php-ext-install \
    pdo_mysql

WORKDIR /app/laravel

# Bootstrap
COPY ./bootstrap.sh /usr/local/bin/bootstrap
RUN chmod 544 /usr/local/bin/bootstrap
CMD ["bootstrap"]
