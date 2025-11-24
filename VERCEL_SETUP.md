# 🚀 Configuración de Vercel con Neon PostgreSQL

## Guía Paso a Paso

### Paso 1: Preparar el Proyecto

✅ **Archivos ya configurados:**
- `vercel.json` - Configuración de Vercel
- `neon.env` - Variables de entorno de referencia
- `DatabaseManager.php` - Ya configurado para usar `DATABASE_URL`

### Paso 2: Crear Cuenta en Vercel

1. Ir a [vercel.com](https://vercel.com)
2. Click en "Sign Up"
3. Conectar con tu cuenta de GitHub

### Paso 3: Importar Proyecto

1. En el dashboard de Vercel, click en **"Add New..."** → **"Project"**
2. Conectar tu repositorio de GitHub si no está conectado
3. Seleccionar el repositorio `inventory-system`
4. Click en **"Import"**

### Paso 4: Configurar el Proyecto

En la pantalla de configuración:

- **Framework Preset**: `Other` (o dejar en blanco)
- **Root Directory**: (dejar vacío)
- **Build Command**: (dejar vacío)
- **Output Directory**: `public`
- **Install Command**: (dejar vacío)

**NO cambiar nada más**, el archivo `vercel.json` ya tiene la configuración correcta.

### Paso 5: Configurar Variables de Entorno ⚠️ IMPORTANTE

**ANTES de hacer Deploy**, click en **"Environment Variables"** y agregar:

#### Variable 1: DATABASE_URL
- **Key**: `DATABASE_URL`
- **Value**: 
  ```
  postgresql://neondb_owner:npg_3tOu8ifYZowE@ep-gentle-sky-afrg8hgf-pooler.c-2.us-west-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require
  ```
- **Environments**: Marcar todas (Production, Preview, Development)
- **Sensitive**: ✅ Marcar como "Sensitive" (recomendado)

#### Variable 2: JWT_SECRET
- **Key**: `JWT_SECRET`
- **Value**: `tu-clave-secreta-jwt-muy-segura-cambiar-aqui`
- **Environments**: Marcar todas
- **Sensitive**: ✅ Marcar como "Sensitive"

#### Variable 3: APP_ENV
- **Key**: `APP_ENV`
- **Value**: `production`
- **Environments**: Marcar todas

#### Variable 4: APP_DEBUG
- **Key**: `APP_DEBUG`
- **Value**: `false`
- **Environments**: Marcar todas

#### Variable 5: APP_URL
- **Key**: `APP_URL`
- **Value**: `https://tu-app.vercel.app` (se actualizará automáticamente después del deploy)
- **Environments**: Marcar todas

### Paso 6: Deploy

1. Click en **"Deploy"**
2. Vercel comenzará a construir y desplegar tu aplicación
3. Esperar a que termine el proceso (2-5 minutos)
4. Tu aplicación estará disponible en: `https://tu-app.vercel.app`

### Paso 7: Verificar Conexión

Después del deploy, verificar que todo funcione:

1. **Health Check**:
   ```
   https://tu-app.vercel.app/api/health
   ```
   Debe responder: `{"status":"ok","database":"connected"}`

2. **Login**:
   ```
   https://tu-app.vercel.app/login.php
   ```
   Debe mostrar la página de login

3. **Verificar Base de Datos**:
   - Las tablas se crean automáticamente en la primera ejecución
   - Puedes verificar en Neon Dashboard que las tablas existen

### Paso 8: Actualizar APP_URL (Opcional)

Después del primer deploy, Vercel te dará una URL. Actualiza la variable `APP_URL` con la URL real:

1. Ir a Settings → Environment Variables
2. Editar `APP_URL`
3. Cambiar a: `https://tu-app-real.vercel.app`
4. Hacer un nuevo deploy

## Estructura de Rutas en Vercel

El archivo `vercel.json` configura las siguientes rutas:

- `/api/*` → `public/index.php` (API endpoints)
- `/auth/*` → `public/index.php` (Autenticación)
- `/*.php` → `public/*.php` (Archivos PHP)
- `/login.php` → `public/login.php` (Login)
- `/config.php` → `public/config.php` (Configuración)
- `/*` → `public/index.html` (Frontend Vue.js)

## Troubleshooting

### Error: "Function not found"
- Verificar que `vercel.json` esté en la raíz del proyecto
- Verificar que las rutas estén correctamente configuradas

### Error: "DATABASE_URL no configurada"
- Verificar que la variable esté en "Environment Variables"
- Verificar que esté marcada para todos los environments (Production, Preview, Development)
- Hacer un nuevo deploy después de agregar variables

### Error: "Connection refused"
- Verificar que la URL de Neon sea correcta
- Verificar que Neon permita conexiones externas (por defecto sí)

### Error: "SSL required"
- Asegurarse de que la URL incluya `?sslmode=require`
- Neon requiere SSL para todas las conexiones

### La aplicación no carga
- Verificar los logs en Vercel Dashboard → Deployments → Logs
- Verificar que `vercel.json` esté correctamente formateado

## Ventajas de Vercel

- ✅ **Gratis** para proyectos personales
- ✅ **Deploy automático** desde GitHub
- ✅ **CDN global** para mejor rendimiento
- ✅ **SSL automático** (HTTPS)
- ✅ **Preview deployments** para cada PR
- ✅ **Soporte PHP** con @vercel/php

## Soporte

- **Documentación Vercel**: https://vercel.com/docs
- **Vercel PHP**: https://vercel.com/docs/functions/serverless-functions/runtimes/php
- **Dashboard**: https://vercel.com/dashboard

