# Sistema de Inventario - Documentación

## Descripción
Sistema de gestión de inventario inspirado en Mouser Electronics, desarrollado con PHP y Vue.js.

## Características Principales

### 🏪 Gestión de Productos
- **CRUD completo** de productos con SKU único
- **Categorización** jerárquica de productos
- **Gestión de stock** con niveles mínimos y máximos
- **Especificaciones técnicas** en formato JSON
- **Imágenes** y documentos técnicos
- **Códigos de barras** automáticos

### 📦 Control de Inventario
- **Movimientos de stock** (entrada, salida, ajustes)
- **Alertas de stock bajo** automáticas
- **Sugerencias de reorden** basadas en historial
- **Trazabilidad completa** de movimientos
- **Valoración** del inventario en tiempo real

### 🏢 Gestión de Proveedores
- **Información completa** de proveedores
- **Términos de pago** y tiempos de entrega
- **Métricas de rendimiento** por proveedor
- **Integración** con APIs externas (Mouser, DigiKey, Newark)

### 📊 Reportes y Análisis
- **Dashboard** con estadísticas clave
- **Reportes de inventario** (PDF, Excel, CSV)
- **Análisis de valor** por categoría
- **Reportes de movimientos** de stock
- **Exportación** de datos

### 🔐 Autenticación y Seguridad
- **JWT** para autenticación
- **Roles de usuario** (Admin, Manager, Employee, Viewer)
- **Validación** de datos de entrada
- **CORS** configurado para APIs

## Tecnologías Utilizadas

### Backend (PHP)
- **Slim Framework 4** - Framework web ligero
- **MySQL** - Base de datos relacional
- **JWT** - Autenticación con tokens
- **PhpSpreadsheet** - Manejo de archivos Excel
- **TCPDF** - Generación de PDFs
- **Guzzle HTTP** - Cliente HTTP para APIs externas
- **Respect Validation** - Validación de datos
- **Monolog** - Logging

### Frontend
- **Vue.js 3** - Framework JavaScript reactivo
- **Bootstrap 5** - Framework CSS
- **Font Awesome** - Iconografía
- **Axios** - Cliente HTTP (opcional)

### APIs Externas
- **Mouser Electronics API** - Catálogo de componentes
- **DigiKey API** - Información de productos
- **Newark API** - Datos de proveedores

## Instalación

### Requisitos
- PHP 8.1 o superior
- MySQL 5.7 o superior (opcional - funciona con datos de demo)
- Composer
- Servidor web (Apache/Nginx)

### Instalación Local

1. **Clonar el repositorio**
```bash
git clone <repository-url>
cd inventory-system
```

2. **Instalar dependencias**
```bash
composer install
```

3. **Configurar variables de entorno**
```bash
cp env.example .env
# Editar .env con tus configuraciones
```

4. **Crear base de datos** (opcional)
```bash
mysql -u root -p < database/schema.sql
```

5. **Configurar servidor web**
- Apuntar el document root a `public/`
- Configurar URL rewriting para Slim

6. **Iniciar servidor de desarrollo**
```bash
php -S localhost:8080 -t public/
```

### Despliegue con Neon PostgreSQL

#### ⚠️ Importante: Neon es solo la base de datos
**Neon** proporciona PostgreSQL, pero necesitas **otro servicio** para alojar la aplicación PHP (Render, Railway, Fly.io, etc.)

#### Paso 1: Base de Datos Neon (Ya configurado ✅)
- **Proyecto**: `snowy-sunset-62775177`
- **Connection String**: Ya configurada

#### Paso 2: Elegir Hosting para la Aplicación

**Opción A: Vercel.com (Recomendado - Gratis) ⭐**
1. Crear cuenta en [vercel.com](https://vercel.com)
2. Add New → Project → Conectar GitHub
3. Seleccionar repositorio `inventory-system`
4. En "Environment Variables" agregar:
   ```
   DATABASE_URL=postgresql://neondb_owner:npg_3tOu8ifYZowE@ep-gentle-sky-afrg8hgf-pooler.c-2.us-west-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require
   JWT_SECRET=tu-clave-secreta-jwt-muy-segura
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://tu-app.vercel.app
   ```
   ⚠️ Marcar `DATABASE_URL` y `JWT_SECRET` como "Sensitive"
5. Deploy automático
6. Tu app estará en: `https://tu-app.vercel.app`

**Ver `HOSTING_SETUP.md` para instrucciones detalladas de Vercel, Render, Railway y Fly.io**

#### Paso 3: Verificar
- Health: `https://tu-app-url.com/api/health`
- Debe mostrar: `{"status":"ok","database":"connected"}`

## Estructura del Proyecto

```
inventory-system/
├── public/                 # Archivos públicos
│   ├── index.php          # Punto de entrada
│   └── index.html         # Frontend Vue.js
├── src/                   # Código fuente PHP
│   ├── Config/            # Configuración
│   ├── Controllers/       # Controladores
│   ├── Models/           # Modelos de datos
│   ├── Services/         # Servicios de negocio
│   ├── Routes/           # Definición de rutas
│   └── Middleware/       # Middleware
├── database/             # Scripts de base de datos
├── uploads/             # Archivos subidos
├── vendor/              # Dependencias Composer
├── composer.json        # Configuración Composer
└── .env                 # Variables de entorno
```

## API Endpoints

### Autenticación
- `POST /api/auth/login` - Iniciar sesión
- `POST /api/auth/register` - Registro de usuario
- `GET /api/auth/me` - Información del usuario actual

### Productos
- `GET /api/products` - Listar productos
- `GET /api/products/{id}` - Obtener producto
- `POST /api/products` - Crear producto
- `PUT /api/products/{id}` - Actualizar producto
- `DELETE /api/products/{id}` - Eliminar producto
- `PATCH /api/products/{id}/stock` - Actualizar stock
- `GET /api/products/low-stock` - Productos con stock bajo

### Categorías
- `GET /api/categories` - Listar categorías
- `GET /api/categories/{id}` - Obtener categoría
- `POST /api/categories` - Crear categoría
- `PUT /api/categories/{id}` - Actualizar categoría
- `DELETE /api/categories/{id}` - Eliminar categoría

### Proveedores
- `GET /api/suppliers` - Listar proveedores
- `GET /api/suppliers/{id}` - Obtener proveedor
- `POST /api/suppliers` - Crear proveedor
- `PUT /api/suppliers/{id}` - Actualizar proveedor
- `DELETE /api/suppliers/{id}` - Eliminar proveedor

### Reportes
- `GET /api/reports/inventory/summary` - Resumen de inventario
- `GET /api/reports/inventory/low-stock` - Reporte de stock bajo
- `GET /api/reports/products/export` - Exportar productos

## Configuración

### Variables de Entorno (.env)
```env
# Base de Datos
DB_HOST=localhost
DB_PORT=3306
DB_NAME=inventory_system
DB_USER=root
DB_PASS=

# JWT
JWT_SECRET=your-super-secret-jwt-key
JWT_EXPIRATION=86400

# Aplicación
APP_NAME="Sistema de Inventario"
APP_ENV=development
APP_DEBUG=true

# APIs Externas
MOUSER_API_KEY=your-mouser-api-key
DIGIKEY_API_KEY=your-digikey-api-key
NEWARK_API_KEY=your-newark-api-key
```

## Uso

### 1. Acceso al Sistema
- Abrir `http://localhost:8080` en el navegador
- Usar credenciales: `admin@inventory.com` / `password`

### 2. Gestión de Productos
- Navegar a "Productos" en el menú lateral
- Usar el botón "Nuevo Producto" para agregar productos
- Filtrar por categoría o proveedor
- Buscar por SKU o nombre

### 3. Dashboard
- Ver estadísticas generales del inventario
- Monitorear productos con stock bajo
- Revisar productos recientes

### 4. Reportes
- Generar reportes de inventario
- Exportar datos a Excel, PDF o CSV
- Analizar tendencias de stock

## Desarrollo

### Agregar Nuevas Funcionalidades

1. **Crear Modelo** en `src/Models/`
2. **Crear Controlador** en `src/Controllers/`
3. **Definir Rutas** en `src/Routes/`
4. **Agregar Validaciones** en `src/Middleware/`
5. **Actualizar Frontend** en `public/index.html`

### Testing
```bash
composer test
```

### Code Standards
```bash
composer cs-check
composer cs-fix
```

## Contribución

1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver `LICENSE` para más detalles.

## Soporte

Para soporte técnico o preguntas:
- Crear un issue en GitHub
- Contactar al equipo de desarrollo
- Revisar la documentación de la API

---

**Desarrollado con ❤️ para la gestión eficiente de inventarios**
