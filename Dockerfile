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

# Copiar todo el proyecto al directorio raíz de FrankenPHP
COPY . /app

# Permisos correctos
RUN chown -R www-data:www-data /app

# Puerto por defecto (Railway lo sobreescribe con $PORT)
EXPOSE 80

# Variable de entorno por defecto
ENV PORT=80 \
    SERVER_NAME=":80"
