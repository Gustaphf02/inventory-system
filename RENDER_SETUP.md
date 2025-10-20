# 🚀 Configuración para Render.com - Guía Paso a Paso

## 🚨 Solución del Error "Repositorio no encontrado"

### Problema Común:
- Render no encuentra tu repositorio aunque esté en GitHub
- Esto suele ser por configuración incorrecta o repositorio privado

## ✅ Soluciones

### **Opción 1: Configuración Manual en Render**

1. **Cambiar el método de conexión:**
   - En lugar de usar "Repositorio público de Git"
   - Usa **"Proveedor de Git"** (GitHub)

2. **Configuración paso a paso:**
   ```
   ✅ Código fuente: Proveedor de Git
   ✅ Conectar GitHub
   ✅ Seleccionar: Gustaphf02/inventory-system
   ✅ Nombre: inventory-system (o el que prefieras)
   ✅ Idioma: PHP (cambiar de "Nodo" a "PHP")
   ✅ Sucursal: main
   ✅ Región: Oregon (US West) o tu preferencia
   ```

3. **Comandos de Build (Importante):**
   ```
   Build Command: composer install --no-dev --optimize-autoloader
   Start Command: php -S 0.0.0.0:$PORT -t public
   ```

### **Opción 2: Repositorio Público (Recomendado)**

Si tu repositorio es privado, hazlo público temporalmente:

1. **Ir a GitHub:**
   - `https://github.com/Gustaphf02/inventory-system`
   - Settings → General → scroll down hasta "Danger Zone"
   - "Change repository visibility" → "Make public"

2. **En Render:**
   - Usar "Repositorio público de Git"
   - URL: `https://github.com/Gustaphf02/inventory-system`

### **Opción 3: Configuración Automática con render.yaml**

Tu proyecto ya tiene `render.yaml`, solo necesitas:

1. **Asegurar que el archivo esté en GitHub:**
   ```bash
   git add render.yaml
   git commit -m "Add render.yaml for automatic deployment"
   git push origin main
   ```

2. **En Render, usar configuración automática:**
   - Render detectará automáticamente el `render.yaml`
   - Se configurará solo con los valores correctos

## 🔧 Configuración Detallada

### Variables de Entorno Requeridas:
```
APP_ENV=production
APP_DEBUG=false
JWT_SECRET=una-clave-super-secreta-y-larga-minimo-32-caracteres
```

### Estructura Verificada:
```
✅ composer.json      - Dependencias PHP
✅ render.yaml        - Configuración automática
✅ public/            - Directorio público
✅ public/index.php   - Punto de entrada
✅ .gitignore         - Archivos excluidos
```

## 🎯 Pasos Finales

1. **En Render Dashboard:**
   - Clic en "Create Web Service"
   - Esperar el build (2-5 minutos)
   - Verificar logs si hay errores

2. **URLs que funcionarán:**
   - Sistema: `https://tu-app.onrender.com/`
   - Demo: `https://tu-app.onrender.com/demo.php`
   - Config: `https://tu-app.onrender.com/config.php`
   - Health: `https://tu-app.onrender.com/api/health`

## 🛠️ Troubleshooting

### Si sigue sin funcionar:
1. **Verificar que el repositorio sea accesible:**
   - Abrir `https://github.com/Gustaphf02/inventory-system` en navegador
   - Debe mostrar el código

2. **Verificar credenciales GitHub:**
   - En Render, reconectar GitHub si es necesario
   - Verificar permisos del repositorio

3. **Usar método alternativo:**
   - Descargar ZIP del repositorio
   - Subirlo manualmente a Render (más laborioso)

## ⚡ Configuración Rápida

**Si quieres lo más rápido:**
1. Hacer repositorio público temporalmente
2. Usar "Repositorio público de Git" en Render
3. URL: `https://github.com/Gustaphf02/inventory-system`
4. Idioma: PHP
5. Build: `composer install --no-dev --optimize-autoloader`
6. Start: `php -S 0.0.0.0:$PORT -t public`
