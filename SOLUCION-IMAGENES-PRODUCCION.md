# 🖼️ GUÍA PARA SOLUCIONAR IMÁGENES EN PRODUCCIÓN

## ⚠️ Problema: Imágenes de productos no se ven en producción

### 🔍 **Diagnóstico del problema:**
Las imágenes están almacenadas en `storage/app/public/products/` pero no son accesibles desde el navegador porque falta el enlace simbólico.

### 📋 **Pasos para solucionarlo en Laravel Cloud:**

#### 1️⃣ **Verificar configuración en .env de producción**
Asegúrate de que tu `.env` en producción contenga:
```env
FILAMENT_FILESYSTEM_DISK=public
FILESYSTEM_DISK=public
```

#### 2️⃣ **Ejecutar comandos en el terminal de Laravel Cloud**
```bash
# Crear enlace simbólico (COMANDO PRINCIPAL)
php artisan storage:link

# Si el comando anterior falla, forzar:
php artisan storage:link --force

# Verificar que se creó correctamente:
ls -la public/storage
```

#### 3️⃣ **Publicar assets de FilamentPHP**
```bash
php artisan filament:assets
php artisan filament:upgrade
```

#### 4️⃣ **Optimizar para producción**
```bash
php artisan config:cache
php artisan route:cache
php artisan optimize
```

#### 5️⃣ **Verificación**
```bash
# Verificar estado de la aplicación
php artisan about

# Probar acceso a una imagen específica:
# https://tu-dominio.com/storage/products/nombre-imagen.png
```

### 🔧 **Si las imágenes siguen sin verse:**

#### ✅ **Verificar estructura de archivos:**
- Las imágenes deben estar en: `storage/app/public/products/`
- El enlace debe apuntar desde: `public/storage` → `storage/app/public`

#### ✅ **Verificar URL de acceso:**
- URL correcta: `https://tu-dominio.com/storage/products/imagen.png`
- URL incorrecta: `https://tu-dominio.com/storage/app/public/products/imagen.png`

#### ✅ **Verificar configuración de FilamentPHP:**
En `app/Filament/Resources/ProductResource.php` debe estar:
```php
FileUpload::make('image')
    ->disk('public')
    ->directory('products')
```

### 🚨 **Comandos críticos para ejecutar:**

```bash
# 1. PRINCIPAL - Crear enlace simbólico
php artisan storage:link

# 2. Si hay problemas de permisos (solo en servidores propios)
chmod -R 755 storage/
chmod -R 755 public/storage

# 3. Verificar que funciona
php artisan tinker
>>> Storage::disk('public')->exists('products');
>>> exit
```

### 💡 **Notas importantes:**

1. **Laravel Cloud maneja automáticamente los permisos de archivos**
2. **El comando `php artisan storage:link` es OBLIGATORIO en producción**
3. **Si cambias la configuración del disco, debes recrear el enlace**
4. **Las imágenes subidas en local NO se sincronizan automáticamente con producción**

### ✅ **Verificación final:**
Después de ejecutar los comandos, accede a:
```
https://inventory-app-main-qvihcj.laravel.cloud/storage/products/[nombre-de-imagen].png
```

Si ves la imagen, ¡problema resuelto! 🎉
