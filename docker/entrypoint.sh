#!/bin/bash

set -e

echo "Inicializando Ignis..."

if [ ! -d "vendor" ]; then
    echo "Instalando dependências Composer..."
    composer install --no-interaction --prefer-dist
fi

if [ -f artisan ]; then
    php artisan storage:link || true
fi

chmod -R 775 storage bootstrap/cache || true

exec "$@"