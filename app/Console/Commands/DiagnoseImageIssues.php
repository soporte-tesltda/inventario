<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DiagnoseImageIssues extends Command
{
    protected $signature = 'products:diagnose-images';
    protected $description = 'Diagnosticar problemas específicos con las imágenes de productos';

    public function handle()
    {        $this->info('🔍 Diagnóstico completo de imágenes de productos...');
        
        // 1. Verificar enlace simbólico
        $this->info("\n1. Verificando enlace simbólico...");
        $publicStoragePath = public_path('storage');
        $realStoragePath = storage_path('app/public');
        
        if (is_dir($publicStoragePath)) {
            if (is_link($publicStoragePath)) {
                $this->info("✅ Enlace simbólico existe");
                $this->info("   Apunta a: " . readlink($publicStoragePath));
            } else {
                // En Windows, verificar si es un Junction o directorio funcional
                $this->info("✅ Directorio storage existe (Junction/Enlace en Windows)");
            }
            
            // Verificar si puede acceder a archivos en el directorio
            $testPath = $publicStoragePath . DIRECTORY_SEPARATOR . 'products';
            if (is_dir($testPath)) {
                $this->info("   ✅ Directorio products accesible");
            } else {
                $this->info("   ⚠️ Directorio products no accesible");
            }
        } else {
            $this->error("❌ No existe enlace simbólico de storage");
            $this->info("   Ejecuta: php artisan storage:link");
            return;
        }

        // 2. Verificar configuración de FilamentPHP
        $this->info("\n2. Verificando configuración de FilamentPHP...");
        $this->info("   Disco público configurado: " . config('filesystems.disks.public.driver'));
        $this->info("   URL base: " . config('filesystems.disks.public.url'));
        $this->info("   APP_URL: " . config('app.url'));

        // 3. Verificar productos con imágenes
        $this->info("\n3. Verificando productos con imágenes...");
        $products = Product::whereNotNull('image')->limit(5)->get();
        
        foreach ($products as $product) {
            $this->info("\n   📦 Producto ID: {$product->id} - {$product->name}");
            $this->info("       Imagen DB: {$product->image}");
            
            // Verificar si el archivo existe físicamente
            $diskExists = Storage::disk('public')->exists($product->image);
            $this->info("       Existe en disco: " . ($diskExists ? '✅ Sí' : '❌ No'));
            
            // Verificar URL completa
            $fullUrl = Storage::disk('public')->url($product->image);
            $this->info("       URL completa: {$fullUrl}");
            
            // Verificar archivo físico
            $physicalPath = storage_path('app/public/' . $product->image);
            $physicalExists = file_exists($physicalPath);
            $this->info("       Archivo físico: " . ($physicalExists ? '✅ Sí' : '❌ No'));
            
            if ($physicalExists) {
                $fileSize = filesize($physicalPath);
                $this->info("       Tamaño: " . number_format($fileSize / 1024, 2) . " KB");
            }
        }

        // 4. Probar URL de ejemplo
        $this->info("\n4. URLs de prueba:");
        $sampleProduct = Product::whereNotNull('image')->first();
        if ($sampleProduct) {
            $sampleUrl = url('/storage/' . $sampleProduct->image);
            $this->info("   Ejemplo URL: {$sampleUrl}");
            
            // Verificar si la URL es accesible
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'method' => 'HEAD'
                ]
            ]);
            
            $headers = @get_headers($sampleUrl, 1, $context);
            if ($headers && strpos($headers[0], '200') !== false) {
                $this->info("   ✅ URL accesible");
            } else {
                $this->error("   ❌ URL no accesible");
            }
        }

        // 5. Verificar permisos
        $this->info("\n5. Verificando permisos...");
        $storageDir = storage_path('app/public/products');
        if (is_readable($storageDir)) {
            $this->info("   ✅ Directorio storage/app/public/products es legible");
        } else {
            $this->error("   ❌ Directorio storage/app/public/products NO es legible");
        }

        $publicDir = public_path('storage/products');
        if (is_readable($publicDir)) {
            $this->info("   ✅ Directorio public/storage/products es legible");
        } else {
            $this->error("   ❌ Directorio public/storage/products NO es legible");
        }

        $this->info("\n✅ Diagnóstico completado");
    }
}
