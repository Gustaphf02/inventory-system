# 🚀 Configuración para Neon.tech - Guía Paso a Paso

## Información del Proyecto
- **Proyecto ID**: snowy-sunset-62775177
- **Plataforma**: Neon.tech (PostgreSQL Serverless)
- **Connection String**: Configurada ✅

## Configuración Inicial

### 1. Base de Datos en Neon

✅ **Base de datos ya configurada**

Connection String:
```
postgresql://neondb_owner:npg_3tOu8ifYZowE@ep-gentle-sky-afrg8hgf-pooler.c-2.us-west-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require
```

**Nota**: Esta URL ya está lista para usar. Solo necesitas configurarla como variable de entorno.

### 2. Configurar Variables de Entorno

En tu plataforma de hosting (Render, Railway, Fly.io, etc.):

```env
DATABASE_URL=postgresql://neondb_owner:npg_3tOu8ifYZowE@ep-gentle-sky-afrg8hgf-pooler.c-2.us-west-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require
JWT_SECRET=tu-clave-secreta-jwt-muy-segura
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-app-url.com
```

**⚠️ IMPORTANTE**: 
- Reemplaza `tu-clave-secreta-jwt-muy-segura` con una clave segura
- Reemplaza `https://tu-app-url.com` con la URL real de tu aplicación

### 3. Verificar Conexión

El sistema usa `DatabaseManager.php` que automáticamente:
- ✅ Detecta `DATABASE_URL` desde variables de entorno
- ✅ Se conecta a PostgreSQL (Neon)
- ✅ Crea las tablas necesarias automáticamente
- ✅ Maneja errores de conexión

### 4. Estructura de Base de Datos

Las siguientes tablas se crean automáticamente:
- `sessions` - Sesiones de usuario
- `products` - Productos del inventario
- `categories` - Categorías
- `suppliers` - Proveedores
- `product_history` - Historial de cambios

## Migración desde Render

Si estás migrando desde Render:

1. **Exportar datos** (si es necesario):
   ```bash
   pg_dump $DATABASE_URL > backup.sql
   ```

2. **Importar a Neon**:
   ```bash
   psql $NEON_DATABASE_URL < backup.sql
   ```

3. **Actualizar variables de entorno** con la nueva `DATABASE_URL` de Neon

4. **Verificar conexión**:
   - Visitar: `https://tu-app.com/api/health`
   - Debería mostrar: `{"status":"ok","database":"connected"}`

## Ventajas de Neon

- ✅ **Serverless**: Escala automáticamente
- ✅ **Sin costo de inactividad**: Solo pagas por uso
- ✅ **Backups automáticos**: Sin configuración adicional
- ✅ **Branching**: Puedes crear branches de la base de datos
- ✅ **PostgreSQL nativo**: Compatible con todas las funciones de PostgreSQL

## Troubleshooting

### Error: "DATABASE_URL no configurada"
- Verificar que la variable `DATABASE_URL` esté configurada en tu plataforma
- Verificar que el formato sea correcto (debe incluir `?sslmode=require`)

### Error: "Connection refused"
- Verificar que la IP de tu servidor esté permitida en Neon
- Neon permite conexiones desde cualquier IP por defecto

### Error: "SSL required"
- Asegurarse de que la URL incluya `?sslmode=require`
- Neon requiere SSL para todas las conexiones

## Soporte

- Documentación Neon: https://neon.tech/docs
- Dashboard: https://console.neon.tech/app/projects/snowy-sunset-62775177

