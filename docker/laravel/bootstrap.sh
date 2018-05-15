#!/usr/bin/env sh

# Add driver repository
composer config --no-interaction repositories.scout-elasticsearch-driver \
    '{"type": "package", "package": {"name": "babenkoivan/scout-elasticsearch-driver-local",
    "version": "dev", "dist": {"type": "path", "url": "../scout-elasticsearch-driver",
    "options": {"symlink": true}}}}'

# Require driver package
composer require --no-interaction babenkoivan/scout-elasticsearch-driver-local:@dev
