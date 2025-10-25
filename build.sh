#!/bin/bash
# Script de build para Render con PostgreSQL

echo "=== INSTALANDO EXTENSIONES PHP PARA POSTGRESQL ==="

# Actualizar repositorios
apt-get update

# Instalar extensiones PHP necesarias para PostgreSQL
echo "Instalando extensión PostgreSQL..."
apt-get install -y php-pgsql php-pdo-pgsql

echo "Instalando otras extensiones PHP..."
apt-get install -y php-json php-mbstring php-openssl php-curl

# Verificar que las extensiones se instalaron
echo "Verificando extensiones instaladas..."
php -m | grep pgsql
php -m | grep pdo_pgsql
php -m | grep json
php -m | grep mbstring

# Instalar dependencias de Composer
echo "Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader

echo "=== BUILD COMPLETADO ==="
