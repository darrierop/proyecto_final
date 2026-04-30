#!/bin/sh
export SERVER_NAME=":${PORT:-80}"
echo ">> Iniciando FrankenPHP en $SERVER_NAME"
exec frankenphp run --config /etc/caddy/Caddyfile --adapter caddyfile