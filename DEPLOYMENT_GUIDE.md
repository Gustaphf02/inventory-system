# 🚀 Guía de Despliegue - Sistema de Inventario

## ⚠️ Importante: Neon vs Hosting

- **Neon**: Solo proporciona la **base de datos PostgreSQL** (ya configurada ✅)
- **Hosting**: Necesitas un servicio separado para **alojar tu aplicación PHP**

## Opciones de Hosting Recomendadas

### Opción 1: Render.com (Recomendado - Gratis)

1. **Ir a Render Dashboard**
   - Visita: https://dashboard.render.com
   - Inicia sesión con GitHub

2. **Crear Nuevo Web Service**
   - Click en "New +" → "Web Service"
   - Conecta tu repositorio GitHub
   - Selecciona el repositorio `inventory-system`

3. **Configuración del Servicio**
   - **Name**: `inventory-system` (o el que prefieras)
   - **Environment**: `Docker`
   - **Region**: Elige la más cercana
   - **Branch**: `main` (o tu rama principal)
   - **Root Directory**: (dejar vacío)
   - **Dockerfile Path**: `./Dockerfile`
   - **Docker Context**: (dejar vacío)

4. **Variables de Entorno** (MUY IMPORTANTE)
   
   Click en "Environment" y agrega estas variables:
   
   ```
   DATABASE_URL=postgresql://neondb_owner:npg_3tOu8ifYZowE@ep-gentle-sky-afrg8hgf-pooler.c-2.us-west-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require
   ```
   
   Y también:
   ```
   JWT_SECRET=tu-clave-secreta-jwt-muy-segura-cambiar-esta
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://tu-app.onrender.com
   ```

5. **Deploy**
   - Click en "Create Web Service"
   - Render construirá y desplegará automáticamente
   - Espera a que termine (5-10 minutos)
   - Tu app estará en: `https://tu-app.onrender.com`

---

### Opción 2: Railway.app (Alternativa - Gratis con límites)

1. **Ir a Railway**
   - Visita: https://railway.app
   - Inicia sesión con GitHub

2. **Nuevo Proyecto**
   - Click en "New Project"
   - "Deploy from GitHub repo"
   - Selecciona `inventory-system`

3. **Configurar Variables**
   - Click en el servicio desplegado
   - Ve a "Variables"
   - Agrega `DATABASE_URL` con tu connection string de Neon

4. **Deploy**
   - Railway detectará automáticamente el Dockerfile
   - Desplegará automáticamente

---

### Opción 3: Fly.io (Alternativa)

1. **Instalar Fly CLI**
   ```bash
   curl -L https://fly.io/install.sh | sh
   ```

2. **Login**
   ```bash
   fly auth login
   ```

3. **Crear App**
   ```bash
   fly launch
   ```

4. **Configurar Variables**
   ```bash
   fly secrets set DATABASE_URL="postgresql://neondb_owner:npg_3tOu8ifYZowE@ep-gentle-sky-afrg8hgf-pooler.c-2.us-west-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require"
   fly secrets set JWT_SECRET="tu-clave-secreta"
   ```

---

## 📋 Checklist de Configuración

### ✅ Base de Datos (Neon) - YA CONFIGURADA
- [x] Proyecto creado en Neon
- [x] Connection String obtenida
- [x] Base de datos lista

### ⚙️ Hosting de Aplicación - POR HACER
- [ ] Elegir plataforma de hosting (Render/Railway/Fly.io)
- [ ] Crear cuenta y conectar repositorio
- [ ] Configurar variable `DATABASE_URL` con la connection string de Neon
- [ ] Configurar otras variables de entorno (JWT_SECRET, APP_URL, etc.)
- [ ] Desplegar aplicación
- [ ] Verificar que funciona: `https://tu-app.com/api/health`

---

## 🔧 Variables de Entorno Requeridas

En tu plataforma de hosting, configura estas variables:

```env
# OBLIGATORIA - Connection String de Neon
DATABASE_URL=postgresql://neondb_owner:npg_3tOu8ifYZowE@ep-gentle-sky-afrg8hgf-pooler.c-2.us-west-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require

# OBLIGATORIA - Clave secreta para JWT (cambiar por una segura)
JWT_SECRET=tu-clave-secreta-jwt-muy-segura-cambiar-esta

# OBLIGATORIA - URL de tu aplicación
APP_URL=https://tu-app.onrender.com

# OPCIONALES (pero recomendadas)
APP_ENV=production
APP_DEBUG=false
```

---

## ✅ Verificación Post-Despliegue

1. **Verificar Health Check**
   ```
   https://tu-app.com/api/health
   ```
   Debería responder: `{"status":"ok","database":"connected"}`

2. **Verificar Base de Datos**
   - Las tablas se crean automáticamente
   - Puedes verificar en Neon Dashboard que las tablas existen

3. **Probar la Aplicación**
   - Visita: `https://tu-app.com`
   - Deberías ver la página de login
   - Intenta iniciar sesión

---

## 🆘 Troubleshooting

### Error: "DATABASE_URL no configurada"
- Verifica que la variable esté configurada en tu plataforma de hosting
- Verifica que el nombre sea exactamente `DATABASE_URL` (mayúsculas)

### Error: "Connection refused"
- Verifica que la connection string sea correcta
- Verifica que Neon esté activo (no en pausa)

### Error: "SSL required"
- Asegúrate de que la URL incluya `?sslmode=require&channel_binding=require`

### La app no inicia
- Revisa los logs en tu plataforma de hosting
- Verifica que el Dockerfile esté correcto
- Verifica que todas las variables de entorno estén configuradas

---

## 📞 Soporte

- **Neon Dashboard**: https://console.neon.tech/app/projects/snowy-sunset-62775177
- **Render Docs**: https://render.com/docs
- **Railway Docs**: https://docs.railway.app
- **Fly.io Docs**: https://fly.io/docs

