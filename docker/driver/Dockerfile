FROM composer:latest

WORKDIR /app/driver

# Bootstrap
COPY docker/driver/bootstrap /usr/local/bin/bootstrap
RUN chmod 544 /usr/local/bin/bootstrap
CMD ["bootstrap"]
