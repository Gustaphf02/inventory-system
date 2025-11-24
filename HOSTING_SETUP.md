# 🌐 Configuración de Hosting para la Aplicación

## ⚠️ Importante: Neon vs Hosting

- **Neon**: Solo proporciona la base de datos PostgreSQL ✅ (Ya configurado)
- **Hosting**: Necesitas otro servicio para alojar tu aplicación PHP

## Opciones de Hosting Recomendadas

### Opción 1: Vercel.com (Recomendado - Gratis) ⭐

1. **Crear cuenta en Vercel**
   - Ir a [vercel.com](https://vercel.com)
   - Crear cuenta con GitHub

2. **Importar Proyecto**
   - Click en "Add New..." → "Project"
   - Conectar tu repositorio de GitHub
   - Seleccionar el repositorio `inventory-system`
   - Click en "Import"

3. **Configuración del Proyecto**
   - **Framework Preset**: Other
   - **Root Directory**: (dejar vacío o poner `./`)
   - **Build Command**: (dejar vacío)
   - **Output Directory**: `public`
   - **Install Command**: (dejar vacío)

4. **Variables de Entorno** (IMPORTANTE)
   Click en "Environment Variables" y agregar:
   ```
   DATABASE_URL=postgresql://neondb_owner:npg_3tOu8ifYZowE@ep-gentle-sky-afrg8hgf-pooler.c-2.us-west-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require
   JWT_SECRET=tu-clave-secreta-jwt-muy-segura-cambiar-aqui
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://tu-app.vercel.app
   ```
   
   **Nota**: Marca `DATABASE_URL` y `JWT_SECRET` como "Sensitive" para ocultarlas

5. **Deploy**
   - Click en "Deploy"
   - Vercel construirá y desplegará automáticamente
   - Tu app estará en: `https://tu-app.vercel.app`

6. **Configuración Adicional**
   - El archivo `vercel.json` ya está configurado
   - Vercel detectará automáticamente las rutas PHP y HTML

---

### Opción 2: Render.com (Alternativa - Gratis)

1. **Crear cuenta en Render**
   - Ir a [render.com](https://render.com)
   - Crear cuenta gratuita

2. **Crear nuevo Web Service**
   - Click en "New +" → "Web Service"
   - Conectar tu repositorio de GitHub
   - Seleccionar el repositorio `inventory-system`

3. **Configuración del servicio**
   - **Name**: `inventory-system` (o el que prefieras)
   - **Environment**: `Docker`
   - **Region**: Cualquiera (US es más rápido)
   - **Branch**: `main` (o tu rama principal)
   - **Root Directory**: (dejar vacío)
   - **Dockerfile Path**: `./Dockerfile`

4. **Variables de Entorno** (IMPORTANTE)
   Click en "Environment" y agregar:
   ```
   DATABASE_URL=postgresql://neondb_owner:npg_3tOu8ifYZowE@ep-gentle-sky-afrg8hgf-pooler.c-2.us-west-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require
   JWT_SECRET=tu-clave-secreta-jwt-muy-segura-cambiar-aqui
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://tu-app.onrender.com
   ```

5. **Start Command**
   ```
   ./start.sh
   ```

6. **Deploy**
   - Click en "Create Web Service"
   - Render construirá y desplegará automáticamente
   - Tu app estará en: `https://tu-app.onrender.com`

---

### Opción 2: Railway.app (Alternativa - Gratis)

1. **Crear cuenta en Railway**
   - Ir a [railway.app](https://railway.app)
   - Crear cuenta con GitHub

2. **Nuevo Proyecto**
   - Click en "New Project"
   - "Deploy from GitHub repo"
   - Seleccionar `inventory-system`

3. **Variables de Entorno**
   - Click en el servicio → "Variables"
   - Agregar:
   ```
   DATABASE_URL=postgresql://neondb_owner:npg_3tOu8ifYZowE@ep-gentle-sky-afrg8hgf-pooler.c-2.us-west-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require
   JWT_SECRET=tu-clave-secreta-jwt-muy-segura
   APP_ENV=production
   APP_DEBUG=false
   ```

4. **Deploy**
   - Railway detectará automáticamente el Dockerfile
   - Desplegará automáticamente

---

### Opción 3: Fly.io (Alternativa - Gratis)

1. **Instalar Fly CLI**
   ```bash
   curl -L https://fly.io/install.sh | sh
   ```

2. **Login**
   ```bash
   fly auth login
   ```

3. **Crear app**
   ```bash
   fly launch
   ```

4. **Configurar variables**
   ```bash
   fly secrets set DATABASE_URL="postgresql://neondb_owner:npg_3tOu8ifYZowE@ep-gentle-sky-afrg8hgf-pooler.c-2.us-west-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require"
   fly secrets set JWT_SECRET="tu-clave-secreta-jwt-muy-segura"
   fly secrets set APP_ENV="production"
   ```

5. **Deploy**
   ```bash
   fly deploy
   ```

---

## Verificación Post-Deploy

Después de desplegar, verifica que todo funcione:

1. **Health Check**
   ```
   https://tu-app-url.com/api/health
   ```
   Debe responder: `{"status":"ok","database":"connected"}`

2. **Login**
   ```
   https://tu-app-url.com/login.php
   ```
   Debe mostrar la página de login

3. **Verificar Base de Datos**
   - Las tablas se crean automáticamente
   - Puedes verificar en Neon Dashboard que las tablas existen

---

## Resumen de Pasos

1. ✅ **Neon**: Base de datos configurada (snowy-sunset-62775177)
2. ⏳ **Hosting**: Elegir Render/Railway/Fly.io
3. ⏳ **Variables**: Configurar `DATABASE_URL` con la connection string de Neon
4. ⏳ **Deploy**: Desplegar la aplicación
5. ⏳ **Verificar**: Comprobar que funciona

---

## Troubleshooting

### Error: "DATABASE_URL no configurada"
- Verificar que la variable esté configurada en el dashboard del hosting
- Verificar que el nombre sea exactamente `DATABASE_URL` (mayúsculas)

### Error: "Connection refused"
- Verificar que la URL de Neon sea correcta
- Verificar que Neon permita conexiones externas (por defecto sí)

### Error: "SSL required"
- Asegurarse de que la URL incluya `?sslmode=require`
- Neon requiere SSL para todas las conexiones

---

## ¿Necesitas ayuda?

- **Render**: https://render.com/docs
- **Railway**: https://docs.railway.app
- **Fly.io**: https://fly.io/docs
- **Neon**: https://neon.tech/docs

