# TROUBLESHOOTING ERROR 500 - INVENTARIO TES LTDA

## 🔍 PASOS PARA DIAGNOSTICAR ERROR 500

### 1️⃣ Verificar Logs del Servidor
```bash
# En el servidor, revisar logs de Laravel
tail -f storage/logs/laravel.log

# O revisar los logs del servidor web
tail -f /var/log/nginx/error.log
tail -f /var/log/apache2/error.log
```

### 2️⃣ Configuración de Producción (.env)
Asegúrate de que tu archivo `.env` en producción tenga:
```
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:TU_CLAVE_AQUÍ
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=tu_base_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

LOG_LEVEL=error
CACHE_STORE=file
```

### 3️⃣ Comandos Críticos para Producción
```bash
# Instalar dependencias de producción
composer install --no-dev --optimize-autoloader

# Generar clave de aplicación si no existe
php artisan key:generate

# Migrar base de datos
php artisan migrate --force

# Configurar permisos de Filament
php artisan shield:install --fresh

# Cachear configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Crear enlace simbólico para storage
php artisan storage:link

# Optimizar aplicación
php artisan optimize
```

### 4️⃣ Permisos de Archivos (Linux/Unix)
```bash
# Permisos para el directorio de la aplicación
chown -R www-data:www-data /path/to/your/app

# Permisos específicos
chmod -R 755 /path/to/your/app
chmod -R 775 /path/to/your/app/storage
chmod -R 775 /path/to/your/app/bootstrap/cache
```

### 5️⃣ Problemas Comunes y Soluciones

#### ❌ Error: "APP_KEY not found"
```bash
php artisan key:generate
```

#### ❌ Error: "Connection refused" (Base de datos)
- Verificar credenciales de DB en .env
- Verificar que el servicio MySQL esté corriendo
- Verificar que la base de datos existe

#### ❌ Error: "Permission denied"
- Verificar permisos de storage/ y bootstrap/cache/
- Verificar que el servidor web tenga acceso a los archivos

#### ❌ Error: "Class not found"
```bash
composer dump-autoload --optimize
php artisan clear-compiled
php artisan optimize:clear
```

### 6️⃣ Verificación Post-Deploy
```bash
# Verificar estado de la aplicación
php artisan about

# Verificar que las tablas existen
php artisan migrate:status

# Verificar permisos de Filament
php artisan shield:doctor

# Probar conexión a base de datos
php artisan tinker
>>> DB::connection()->getPdo();
```

### 7️⃣ Variables de Entorno Críticas
- ✅ APP_KEY debe estar configurado
- ✅ APP_ENV=production
- ✅ APP_DEBUG=false  
- ✅ DB_* variables configuradas correctamente
- ✅ APP_URL debe coincidir con tu dominio

### 8️⃣ Si Persiste el Error
1. Habilitar temporalmente debug: `APP_DEBUG=true`
2. Revisar el error específico en el navegador
3. Deshabilitar debug después: `APP_DEBUG=false`
