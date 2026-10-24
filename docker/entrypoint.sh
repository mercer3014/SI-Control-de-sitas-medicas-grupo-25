#!/bin/sh
set -eu

echo "==> Iniciando despliegue de Clínica Vida Salud..."

php artisan storage:link --force >/dev/null 2>&1 || true

echo "==> Ejecutando migraciones de base de datos..."
php artisan migrate --force --no-interaction

echo "==> Sembrando datos iniciales y credenciales de prueba..."
php artisan db:seed --force --no-interaction

echo "==> Optimizando caché de configuraciones, rutas y vistas..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Servidor listo. Escuchando en el puerto ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
