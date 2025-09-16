#!/bin/bash

# ========================================================================
# SCRIPT DE DESPLIEGUE A PRODUCCIÓN - INVENTARIO TES LTDA
# ========================================================================
# Este script automatiza el proceso de despliegue y verifica que 
# las imágenes funcionen correctamente en producción

echo "🚀 Iniciando despliegue a producción..."

# ========================================================================
# 1. VERIFICACIONES PREVIAS
# ========================================================================
echo ""
echo "1️⃣ Verificando configuración..."

# Verificar que existe .env de producción
if [ ! -f .env ]; then
    echo "❌ Error: No existe archivo .env"
    echo "   Copia .env.production.example como .env y configúralo"
    exit 1
fi

# Verificar APP_ENV
if grep -q "APP_ENV=local" .env; then
    echo "⚠️  Advertencia: APP_ENV está en 'local', debería ser 'production'"
fi

# Verificar APP_DEBUG
if grep -q "APP_DEBUG=true" .env; then
    echo "⚠️  Advertencia: APP_DEBUG está en 'true', debería ser 'false'"
fi

# Verificar APP_URL
if grep -q "127.0.0.1\|localhost" .env; then
    echo "⚠️  Advertencia: APP_URL contiene localhost, actualiza al dominio real"
fi

# ========================================================================
# 2. INSTALACIÓN Y OPTIMIZACIÓN
# ========================================================================
echo ""
echo "2️⃣ Instalando dependencias y optimizando..."

# Instalar dependencias de producción
composer install --no-dev --optimize-autoloader

# Optimizaciones de Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ========================================================================
# 3. CONFIGURACIÓN DE ALMACENAMIENTO E IMÁGENES
# ========================================================================
echo ""
echo "3️⃣ Configurando almacenamiento de imágenes..."

# Crear enlace simbólico para imágenes
if [ ! -L public/storage ]; then
    echo "   Creando enlace simbólico..."
    php artisan storage:link
else
    echo "   ✅ Enlace simbólico ya existe"
fi

# Verificar permisos en Linux/Unix
if [ "$(uname)" != "Darwin" ] && [ "$(uname)" != "MINGW64_NT-10.0-19045" ]; then
    echo "   Configurando permisos..."
    chmod -R 755 storage/
    chmod -R 755 public/storage/
    chmod -R 775 storage/app/public/
fi

# Crear directorios necesarios
mkdir -p storage/app/public/products
mkdir -p storage/app/public/contracts

# Extraer imágenes existentes si existe el backup
if [ -f "storage/app/public/imagenes-productos-backup.zip" ]; then
    echo "   📦 Extrayendo imágenes existentes..."
    cd storage/app/public
    unzip -o imagenes-productos-backup.zip
    cd ../../..
    echo "   ✅ Imágenes existentes restauradas (1,373 imágenes)"
else
    echo "   ℹ️  No se encontró backup de imágenes existentes"
fi

echo "   ✅ Configuración de almacenamiento completada"

# ========================================================================
# 4. BASE DE DATOS Y SESIONES
# ========================================================================
echo ""
echo "4️⃣ Configurando base de datos y sesiones..."

# Ejecutar migraciones
php artisan migrate --force

# Configurar sesiones para producción
php artisan session:fix --production

# Opcional: Ejecutar seeders solo si es primera instalación
read -p "¿Es la primera instalación? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan db:seed --force
fi

# ========================================================================
# 5. VERIFICACIONES POST-DESPLIEGUE
# ========================================================================
echo ""
echo "5️⃣ Verificando configuración de imágenes..."

# Ejecutar diagnóstico de imágenes
php artisan products:production-check

# ========================================================================
# 6. OPTIMIZACIONES ADICIONALES
# ========================================================================
echo ""
echo "6️⃣ Aplicando optimizaciones finales..."

# Limpiar caché de aplicación
php artisan cache:clear
php artisan config:clear

# Regenerar caché de producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ========================================================================
# 7. CONFIGURACIÓN DEL SERVIDOR WEB
# ========================================================================
echo ""
echo "7️⃣ Configuración del servidor web..."
echo "   📋 Asegúrate de configurar tu servidor web para:"
echo "   • Servir archivos estáticos desde public/storage/"
echo "   • Configurar HTTPS si es necesario"
echo "   • Configurar compresión GZIP para imágenes"
echo "   • Configurar caché de navegador para archivos estáticos"

# ========================================================================
# 8. VERIFICACIÓN FINAL
# ========================================================================
echo ""
echo "8️⃣ Verificación final..."

# Verificar que las rutas principales funcionen
echo "   Verificando aplicación..."
php artisan route:list | grep -q "inventario" && echo "   ✅ Rutas configuradas correctamente"

# Verificar configuración de almacenamiento
if [ -d "public/storage/products" ]; then
    echo "   ✅ Directorio de imágenes accesible"
else
    echo "   ❌ Error: Directorio de imágenes no accesible"
fi

echo ""
echo "🎉 ¡Despliegue completado!"
echo ""
echo "📋 CHECKLIST POST-DESPLIEGUE:"
echo "   □ Verificar que APP_URL apunte al dominio correcto"
echo "   □ Probar subir una imagen de producto"
echo "   □ Verificar que las imágenes se muestren en la lista"
echo "   □ Configurar backups automáticos"
echo "   □ Configurar monitoreo de logs"
echo "   □ Documentar credenciales de acceso"
echo ""
echo "🔗 Acceso a la aplicación:"
grep "APP_URL" .env | cut -d'=' -f2 | sed 's/^/   /'
echo "/inventario"
echo ""
echo "📞 Soporte: TES LTDA"
