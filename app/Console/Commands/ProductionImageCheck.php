<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ProductionImageCheck extends Command
{
    protected $signature = 'products:production-check';
    protected $description = 'Verificar la configuración de imágenes para producción';

    public function handle()
    {
        $this->info('🚀 Verificación para entorno de producción...');
        
        // 1. Verificar configuración de storage
        $this->info("\n1. Configuración de Storage:");
        $this->info("   Driver público: " . config('filesystems.disks.public.driver'));
        $this->info("   Root: " . config('filesystems.disks.public.root'));
        $this->info("   URL: " . config('filesystems.disks.public.url'));
        
        // 2. Verificar APP_URL
        $this->info("\n2. Configuración de URLs:");
        $appUrl = config('app.url');
        $this->info("   APP_URL: {$appUrl}");
        
        if ($appUrl === 'http://localhost' || $appUrl === 'http://127.0.0.1:8000') {
            $this->warn("   ⚠️ APP_URL debe ser actualizada para producción");
        } else {
            $this->info("   ✅ APP_URL configurada correctamente");
        }
        
        // 3. Verificar enlace simbólico
        $this->info("\n3. Enlace simbólico:");
        $publicStoragePath = public_path('storage');
        
        if (is_dir($publicStoragePath)) {
            $this->info("   ✅ Enlace storage existe");
              // Verificar directorio products
            $productsPath = $publicStoragePath . DIRECTORY_SEPARATOR . 'products';
            if (is_dir($productsPath)) {
                $this->info("   ✅ Directorio products accesible");
            } else {
                $this->error("   ❌ Directorio products no accesible");
            }
        } else {
            $this->error("   ❌ Enlace storage no existe");
            $this->info("   🔧 Solución: php artisan storage:link");
        }
        
        // 4. Verificar permisos (solo en Linux/Unix)
        $this->info("\n4. Permisos (para servidores Linux):");
        if (PHP_OS_FAMILY !== 'Windows') {
            $storageDir = storage_path('app/public');
            $publicDir = public_path('storage');
            
            $storagePerms = substr(sprintf('%o', fileperms($storageDir)), -4);
            $this->info("   storage/app/public: {$storagePerms}");
            
            if (is_dir($publicDir)) {
                $publicPerms = substr(sprintf('%o', fileperms($publicDir)), -4);
                $this->info("   public/storage: {$publicPerms}");
            }
        } else {
            $this->info("   ✅ Windows - No se requiere verificación de permisos Unix");
        }
        
        // 5. Verificar productos de muestra
        $this->info("\n5. Verificación de productos:");
        $productsWithImages = Product::whereNotNull('image')->count();
        $productsWithoutImages = Product::whereNull('image')->count();
        
        $this->info("   Productos con imágenes: {$productsWithImages}");
        $this->info("   Productos sin imágenes: {$productsWithoutImages}");
        
        // 6. Recomendaciones para producción
        $this->info("\n6. 📋 Lista de verificación para producción:");
        $this->info("   □ Configurar APP_URL en .env");
        $this->info("   □ Ejecutar: php artisan storage:link");
        $this->info("   □ Verificar permisos en storage/ (755)");
        $this->info("   □ Verificar permisos en public/storage/ (755)");
        $this->info("   □ Configurar servidor web para servir archivos estáticos");
        $this->info("   □ Optimizar imágenes para web (WebP, compresión)");
        $this->info("   □ Configurar CDN si es necesario");
        
        // 7. Problemas comunes
        $this->info("\n7. 🔧 Solución a problemas comunes:");
        $this->info("   • Imágenes no se ven: Verificar enlace simbólico");
        $this->info("   • Error 404 en storage: php artisan storage:link");
        $this->info("   • Permisos denegados: chmod 755 storage/ -R");
        $this->info("   • URLs incorrectas: Verificar APP_URL en .env");
        
        $this->info("\n✅ Verificación de producción completada");
    }
}
