#!/bin/sh
# Entrypoint: establece SERVER_NAME con el puerto dinámico de Railway
export SERVER_NAME=":${PORT:-80}"
echo ">> Iniciando FrankenPHP en $SERVER_NAME"
exec frankenphp run --config /etc/caddy/Caddyfile --adapter caddyfile
