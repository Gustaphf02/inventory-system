# Render Build Script
# Este archivo se ejecuta durante el build en Render

#!/bin/bash
# Instalar extensiones PHP necesarias para MongoDB

echo "Instalando extensiones PHP para MongoDB..."

# Actualizar repositorios
apt-get update

# Instalar extensión MongoDB
apt-get install -y php-mongodb

# Verificar que la extensión se instaló
php -m | grep mongodb

echo "Extensiones instaladas correctamente"
