# Script de despliegue para producción - PowerShell
Write-Host "🚀 Comandos para ejecutar en producción..." -ForegroundColor Green

Write-Host ""
Write-Host "1️⃣ Configurar entorno de producción:" -ForegroundColor Yellow
Write-Host "composer install --no-dev --optimize-autoloader"

Write-Host ""
Write-Host "2️⃣ Configurar aplicación:" -ForegroundColor Yellow
Write-Host "php artisan config:cache"
Write-Host "php artisan route:cache"
Write-Host "php artisan view:cache"

Write-Host ""
Write-Host "3️⃣ Base de datos:" -ForegroundColor Yellow
Write-Host "php artisan migrate --force"

Write-Host ""
Write-Host "4️⃣ Permisos de FilamentPHP:" -ForegroundColor Yellow
Write-Host "php artisan shield:install --fresh"

Write-Host ""
Write-Host "5️⃣ Assets y archivos:" -ForegroundColor Yellow
Write-Host "php artisan filament:upgrade"
Write-Host "php artisan storage:link"

Write-Host ""
Write-Host "6️⃣ Optimización final:" -ForegroundColor Yellow
Write-Host "php artisan optimize"

Write-Host ""
Write-Host "7️⃣ Verificar estado:" -ForegroundColor Yellow
Write-Host "php artisan about"

Write-Host ""
Write-Host "⚠️  IMPORTANTE:" -ForegroundColor Red
Write-Host "- Asegúrate de que APP_ENV=production en .env"
Write-Host "- Asegúrate de que APP_DEBUG=false en .env"
Write-Host "- Configura correctamente la base de datos"
Write-Host "- Verifica los permisos de archivos y carpetas"
