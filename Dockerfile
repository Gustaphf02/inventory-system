# Dockerfile para Sistema de Inventario PHP
FROM php:8.1-cli

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip pdo pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /app

# Copiar solo composer.json primero para cache de dependencias
COPY composer.json composer.lock ./

# Instalar dependencias PHP (esta capa se cachea si no cambian las dependencias)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar el resto del código
COPY . .

# Configurar permisos
RUN chmod -R 755 /app

# Exponer puerto
EXPOSE $PORT

# Comando de inicio para Render
CMD php -S 0.0.0.0:${PORT:-8080} -t public
