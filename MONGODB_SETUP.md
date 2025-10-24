# Configuración MongoDB Atlas para Render

## Pasos para configurar MongoDB Atlas:

### 1. Crear cuenta en MongoDB Atlas:
1. Ve a https://www.mongodb.com/cloud/atlas
2. Crea una cuenta gratuita
3. Selecciona "Build a Database"
4. Elige "M0 Sandbox" (gratuito)
5. Selecciona región cercana
6. Crea el cluster

### 2. Configurar acceso:
1. En "Database Access", crea un usuario:
   - Username: `inventory_user`
   - Password: `[genera una contraseña segura]`
   - Database User Privileges: `Read and write to any database`

2. En "Network Access", agrega IP:
   - Agrega `0.0.0.0/0` para permitir acceso desde cualquier IP
   - O agrega la IP específica de Render

### 3. Obtener string de conexión:
1. En "Database", haz clic en "Connect"
2. Selecciona "Connect your application"
3. Driver: `PHP`
4. Version: `1.15`
5. Copia el string de conexión

### 4. Configurar en Render:
1. Ve a tu proyecto en Render
2. Settings → Environment
3. Agrega variable:
   - Key: `MONGODB_URI`
   - Value: `mongodb+srv://inventory_user:[PASSWORD]@cluster0.xxxxx.mongodb.net/inventory_db?retryWrites=true&w=majority`

### 5. Verificar instalación:
1. Haz commit y push de los cambios
2. Ve a `https://tu-app.onrender.com/test-mongodb.php`
3. Debe mostrar "MONGODB ATLAS FUNCIONANDO CORRECTAMENTE"

### 6. Migrar datos existentes:
Una vez que MongoDB esté funcionando, los datos se migrarán automáticamente desde archivos JSON.

## Estructura de la base de datos:
- **Database**: `inventory_db`
- **Collection**: `products`
- **Índices únicos**: `sku`, `serial_number`, `label`

## Ventajas de MongoDB Atlas:
- ✅ Gratuito hasta 512MB
- ✅ Persistencia real entre deploys
- ✅ Escalable
- ✅ Backup automático
- ✅ Monitoreo incluido
