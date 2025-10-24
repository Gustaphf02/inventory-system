#!/bin/bash
# Script de build para Render con MongoDB

echo "=== INSTALANDO EXTENSIONES PHP PARA MONGODB ==="

# Actualizar repositorios
apt-get update

# Instalar extensiones PHP necesarias
echo "Instalando extensión MongoDB..."
apt-get install -y php-mongodb

echo "Instalando otras extensiones PHP..."
apt-get install -y php-json php-mbstring php-openssl php-curl

# Verificar que las extensiones se instalaron
echo "Verificando extensiones instaladas..."
php -m | grep mongodb
php -m | grep json
php -m | grep mbstring

# Instalar dependencias de Composer
echo "Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader

echo "=== BUILD COMPLETADO ==="
