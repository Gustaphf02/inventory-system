#!/bin/bash
# Script de build para Render con PostgreSQL

echo "=== INSTALANDO EXTENSIONES PHP PARA POSTGRESQL ==="

# Actualizar repositorios
apt-get update

# Instalar extensiones PHP necesarias para PostgreSQL
echo "Instalando extensión PostgreSQL..."
apt-get install -y php-pgsql php-pdo-pgsql php-common

echo "Instalando otras extensiones PHP..."
apt-get install -y php-json php-mbstring php-openssl php-curl php-xml

# Verificar que las extensiones se instalaron
echo "Verificando extensiones instaladas..."
echo "=== EXTENSIONES PHP INSTALADAS ==="
php -m | grep -E "(pgsql|pdo_pgsql|json|mbstring|openssl|curl|xml)"

# Verificar específicamente PostgreSQL
echo "=== VERIFICACIÓN POSTGRESQL ==="
if php -m | grep -q "pdo_pgsql"; then
    echo "✅ pdo_pgsql instalado correctamente"
else
    echo "❌ pdo_pgsql NO instalado"
fi

if php -m | grep -q "pgsql"; then
    echo "✅ pgsql instalado correctamente"
else
    echo "❌ pgsql NO instalado"
fi

# Instalar dependencias de Composer
echo "Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader

echo "=== BUILD COMPLETADO ==="
