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

# Copiar el proyecto al directorio raíz de la app
COPY . /app

# ⚠️ Copiar el Caddyfile donde FrankenPHP lo espera
COPY Caddyfile /etc/caddy/Caddyfile

# Permisos
RUN chown -R www-data:www-data /app

# Puerto por defecto (Railway sobreescribe con $PORT)
EXPOSE 80

ENV PORT=80
