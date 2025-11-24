# 🚀 Configuración de Hosting: Vercel + Neon

## Stack Tecnológico

- **Frontend**: HTML/CSS/JavaScript (Vue.js)
- **Backend**: PHP 8.2+ con `vercel-php@0.7.4` runtime
- **Base de Datos**: Neon PostgreSQL
- **Hosting**: Vercel

---

## ⚙️ Configuración Inicial

### 1. Crear Proyecto en Vercel

1. **Crear cuenta en Vercel**
   - Ir a [vercel.com](https://vercel.com)
   - Crear cuenta con GitHub

2. **Importar Proyecto**
   - Click en "Add New..." → "Project"
   - Conectar tu repositorio de GitHub
   - Seleccionar el repositorio `inventory-system`
   - Click en "Import"

3. **Configuración del Proyecto**
   - **Framework Preset**: `Other`
   - **Root Directory**: (dejar vacío)
   - **Build Command**: (dejar vacío)
   - **Output Directory**: `public`
   - **Install Command**: (dejar vacío)

### 2. Variables de Entorno (IMPORTANTE)

Click en "Environment Variables" y agregar:

```
DATABASE_URL=postgresql://neondb_owner:npg_3tOu8ifYZowE@ep-gentle-sky-afrg8hgf-pooler.c-2.us-west-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require
JWT_SECRET=tu-clave-secreta-jwt-muy-segura-cambiar-aqui
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-app.vercel.app
```

**⚠️ IMPORTANTE:**
- Marca `DATABASE_URL` y `JWT_SECRET` como "Sensitive" para ocultarlas
- Reemplaza `tu-clave-secreta-jwt-muy-segura-cambiar-aqui` con una clave segura
- Reemplaza `https://tu-app.vercel.app` con la URL real de tu app después del primer deploy

### 3. Deploy

1. Click en "Deploy"
2. Vercel construirá y desplegará automáticamente usando `vercel-php@0.7.4`
3. Tu app estará en: `https://tu-app.vercel.app`

---

## 🔧 Configuración Técnica

### Runtime PHP

El proyecto usa `vercel-php@0.7.4`, un runtime de la comunidad que permite ejecutar PHP en Vercel.

**Configuración en `vercel.json`:**
```json
{
  "functions": {
    "public/**/*.php": {
      "runtime": "vercel-php@0.7.4"
    }
  }
}
```

### Extensiones PHP Soportadas

El runtime `vercel-php` incluye:
- ✅ `pdo` y `pdo_pgsql` (PostgreSQL)
- ✅ `json`, `mbstring`, `openssl`, `curl`, `xml`
- ✅ `zip`, `gd` (para imágenes)

### Estructura de Rutas

Las rutas API se manejan a través de `public/index.php`:
- `/api/*` → `public/index.php`
- `/auth/*` → `public/index.php`
- `/products`, `/categories`, etc. → `public/index.php`
- `/*.php` → Archivos PHP directos
- `/*` → `public/index.html` (frontend)

---

## 🗄️ Configuración de Neon

### Connection String

La base de datos Neon ya está configurada:
```
postgresql://neondb_owner:npg_3tOu8ifYZowE@ep-gentle-sky-afrg8hgf-pooler.c-2.us-west-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require
```

**Proyecto Neon**: `snowy-sunset-62775177`

### Verificación

1. **Dashboard Neon**: https://console.neon.tech/app/projects/snowy-sunset-62775177
2. **Verificar conexión**: Visita `https://tu-app.vercel.app/api/health`
   - Debe responder: `{"status":"ok","database":"connected"}`

---

## 📋 Checklist de Deploy

- [ ] Cuenta en Vercel creada
- [ ] Repositorio conectado a Vercel
- [ ] Variables de entorno configuradas:
  - [ ] `DATABASE_URL` (con la connection string de Neon)
  - [ ] `JWT_SECRET` (clave segura)
  - [ ] `APP_ENV=production`
  - [ ] `APP_DEBUG=false`
  - [ ] `APP_URL` (URL de Vercel)
- [ ] Primer deploy completado
- [ ] Verificar que `/api/health` funciona
- [ ] Verificar que el login funciona
- [ ] Verificar que las rutas API responden correctamente

---

## 🐛 Troubleshooting

### Error: "The package `@vercel/php` is not published"

**Solución**: El proyecto usa `vercel-php@0.7.4` (runtime de la comunidad), no `@vercel/php`. Verifica que `vercel.json` tenga:
```json
"runtime": "vercel-php@0.7.4"
```

### Error: "404 Not Found" para rutas API

**Causa**: Las rutas no están correctamente configuradas.

**Solución**: Verifica que `vercel.json` tenga las rutas correctas:
```json
{
  "src": "/api/(.*)",
  "dest": "/public/index.php"
}
```

### Error: "Database connection failed"

**Causa**: La variable `DATABASE_URL` no está configurada o es incorrecta.

**Solución**:
1. Verifica que `DATABASE_URL` esté en las variables de entorno de Vercel
2. Verifica que la URL incluya `?sslmode=require`
3. Verifica que Neon esté activo y accesible

### Error: "Unexpected token '<', "<!DOCTYPE "... is not valid JSON"

**Causa**: El servidor está devolviendo HTML en lugar de JSON.

**Solución**:
1. Verifica que la ruta esté correctamente configurada en `vercel.json`
2. Verifica que `public/index.php` esté manejando correctamente las rutas API
3. Revisa los logs de Vercel para ver qué está pasando

---

## 📚 Recursos

- **Vercel PHP Runtime**: https://github.com/vercel-community/php
- **Neon Documentation**: https://neon.tech/docs
- **Vercel Documentation**: https://vercel.com/docs

---

## ✅ Verificación Post-Deploy

Después del deploy, verifica:

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

3. **API Endpoints**:
   ```
   https://tu-app.vercel.app/api/products
   https://tu-app.vercel.app/auth/me
   ```
   Deben responder con JSON (después de autenticación)

---

## 🎉 ¡Listo!

Tu aplicación está desplegada en Vercel con Neon PostgreSQL. 

**URL de tu app**: `https://tu-app.vercel.app`
