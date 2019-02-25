#!/bin/sh

echo "Waiting until dependencies are installed"
until docker-compose -f docker-compose.yml exec php find /app/driver/vendor/autoload.php > /dev/null 2>&1; do
    printf '.'
    sleep 5
done
echo ""
echo "Dependencies are installed"
