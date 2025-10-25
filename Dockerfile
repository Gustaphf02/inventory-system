# Dockerfile para Render con PostgreSQL
FROM php:8.2-apache

# Instalar extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar archivos del proyecto
COPY . /var/www/html/

# Instalar dependencias
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# Configurar Apache
RUN a2enmod rewrite
COPY public/.htaccess /var/www/html/.htaccess

# Exponer puerto
EXPOSE 80

# Comando de inicio
CMD ["apache2-foreground"]