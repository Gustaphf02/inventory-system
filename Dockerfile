# Dockerfile para Render con PostgreSQL
FROM php:8.2-apache-bookworm

# Instalar extensiones PHP necesarias (incluyendo las requeridas por PhpSpreadsheet)
RUN apt-get update && apt-get upgrade -y && apt-get install -y \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pgsql gd zip xml curl mbstring iconv \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar archivos del proyecto
COPY . /var/www/html/

# Instalar dependencias
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# Configurar Apache para usar el directorio public
RUN a2enmod rewrite
RUN a2enmod headers
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Exponer puerto
EXPOSE 80

# Comando de inicio
CMD ["apache2-foreground"]