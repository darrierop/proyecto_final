# ============================================================
# Dockerfile — Sistema Académico (FrankenPHP + Railway)
# Puerto dinámico via $PORT
# ============================================================

FROM dunglas/frankenphp:latest

# Instalar extensiones PHP necesarias
RUN install-php-extensions \
    mysqli \
    pdo \
    pdo_mysql \
    opcache \
    mbstring

# Copiar el proyecto
COPY . /app

# Copiar el Caddyfile donde FrankenPHP lo espera
COPY Caddyfile /etc/caddy/Caddyfile

# Copiar y dar permisos al entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Permisos del proyecto
RUN chown -R www-data:www-data /app

EXPOSE 80

# El entrypoint establece SERVER_NAME=$PORT antes de arrancar
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
