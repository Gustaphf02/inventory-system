#!/bin/bash

# Script de inicio para Render
# Este script prepara el entorno y inicia la aplicación

echo "🚀 Iniciando Sistema de Inventario en Render..."

# Verificar que PHP esté disponible
if ! command -v php &> /dev/null; then
    echo "❌ PHP no está disponible"
    exit 1
fi

# Verificar versión de PHP (Render usa PHP 8.1+)
php_version=$(php -r "echo PHP_VERSION;")
echo "📋 Versión de PHP: $php_version"

# Instalar dependencias si composer.json existe
if [ -f "composer.json" ]; then
    echo "📦 Instalando dependencias de Composer..."
    if [ ! -d "vendor" ]; then
        composer install --no-dev --optimize-autoloader --no-interaction
    else
        echo "✅ Dependencias ya instaladas"
    fi
fi

# Crear directorio de uploads si no existe
mkdir -p public/uploads

# Configurar permisos básicos
chmod 755 public/
chmod 644 public/*.php

echo "✅ Preparación completada"

# Obtener puerto desde variable de entorno de Render
PORT=${PORT:-8080}

echo "🌐 Iniciando servidor en puerto $PORT..."

# Iniciar servidor PHP
exec php -S 0.0.0.0:$PORT -t public
