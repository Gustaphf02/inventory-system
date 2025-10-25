# Dockerfile para Render con PostgreSQL
FROM php:8.2-apache

# Instalar extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pgsql gd zip xml curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar archivos del proyecto
COPY . /var/www/html/

# Instalar dependencias
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --ignore-platform-req=ext-gd

# Configurar Apache
RUN a2enmod rewrite
COPY .htaccess /var/www/html/.htaccess

# Exponer puerto
EXPOSE 80

# Comando de inicio
CMD ["apache2-foreground"]