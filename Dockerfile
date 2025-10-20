# Dockerfile para Sistema de Inventario PHP
FROM php:8.1-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip pdo pdo_mysql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Configurar Apache
COPY public /var/www/html
RUN chmod -R 755 /var/www/html

# Configurar puerto
EXPOSE $PORT

# Comando de inicio personalizado para Render
CMD php -S 0.0.0.0:$PORT -t public
