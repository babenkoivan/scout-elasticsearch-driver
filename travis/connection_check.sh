#!/bin/sh

host=$1
port=$2

echo "Attempting to connect to $host:$port"
until docker-compose -f docker-compose.yml exec php nc -z $host $port; do
    printf '.'
    sleep 5
done
echo ""
echo "Connection to $host:$port is established"
