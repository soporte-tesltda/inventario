<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;

class DiagnoseS3Config extends Command
{
    protected $signature = 'app:diagnose-s3';
    protected $description = 'Diagnose S3/Cloudflare R2 configuration and test image URLs';

    public function handle()
    {
        $this->info('🔍 DIAGNÓSTICO COMPLETO DE CONFIGURACIÓN S3/CLOUDFLARE R2');
        $this->newLine();

        // 1. Verificar configuración básica
        $this->info('1. CONFIGURACIÓN BÁSICA:');
        $this->line('   FILESYSTEM_DISK: ' . config('filesystems.default'));
        $this->line('   FILAMENT_FILESYSTEM_DISK: ' . env('FILAMENT_FILESYSTEM_DISK'));
        $this->line('   AWS_BUCKET: ' . env('AWS_BUCKET'));
        $this->line('   AWS_DEFAULT_REGION: ' . env('AWS_DEFAULT_REGION'));
        $this->line('   AWS_ENDPOINT: ' . env('AWS_ENDPOINT'));
        $this->line('   AWS_USE_PATH_STYLE_ENDPOINT: ' . env('AWS_USE_PATH_STYLE_ENDPOINT'));
        $this->newLine();

        // 2. Verificar configuración del disco 'private'
        $this->info('2. CONFIGURACIÓN DISCO "private":');
        $diskConfig = config('filesystems.disks.private');
        foreach ($diskConfig as $key => $value) {
            $this->line("   {$key}: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value));
        }
        $this->newLine();

        // 3. Probar conexión básica con S3
        $this->info('3. TEST DE CONEXIÓN S3:');
        try {
            $files = Storage::disk('private')->files('products');
            $this->line('   ✅ Conexión exitosa con S3');
            $this->line('   📁 Archivos encontrados en products/: ' . count($files));
            
            // Mostrar algunos archivos de ejemplo
            if (count($files) > 0) {
                $this->line('   📋 Primeros 5 archivos:');
                foreach (array_slice($files, 0, 5) as $file) {
                    $this->line('      - ' . $file);
                }
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Error de conexión S3: ' . $e->getMessage());
        }
        $this->newLine();

        // 4. Probar generación de URLs
        $this->info('4. TEST DE GENERACIÓN DE URLs:');
        $product = Product::whereNotNull('image')->first();
        
        if ($product) {
            $this->line('   📦 Producto de prueba: ' . $product->name);
            $this->line('   🖼️  Imagen almacenada: ' . $product->image);
            
            try {
                // URL directa del storage
                $directUrl = Storage::disk('private')->url($product->image);
                $this->line('   🔗 URL directa: ' . $directUrl);
                
                // URL desde el accessor del modelo
                $modelUrl = $product->image_url;
                $this->line('   🔗 URL del modelo: ' . $modelUrl);
                
                // Test HTTP de la URL
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 10,
                        'method' => 'HEAD'
                    ]
                ]);
                
                $headers = @get_headers($directUrl, 1, $context);
                if ($headers && strpos($headers[0], '200') !== false) {
                    $this->line('   ✅ URL accesible (HTTP 200)');
                } else {
                    $this->error('   ❌ URL no accesible: ' . ($headers[0] ?? 'Sin respuesta'));
                }
                
            } catch (\Exception $e) {
                $this->error('   ❌ Error generando URL: ' . $e->getMessage());
            }
        } else {
            $this->warn('   ⚠️  No se encontraron productos con imágenes');
        }
        $this->newLine();

        // 5. Verificar variables de entorno críticas
        $this->info('5. VARIABLES CRÍTICAS:');
        $criticalVars = [
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
            'AWS_BUCKET',
            'AWS_ENDPOINT'
        ];
        
        foreach ($criticalVars as $var) {
            $value = env($var);
            if ($value) {
                $masked = $var === 'AWS_SECRET_ACCESS_KEY' 
                    ? substr($value, 0, 4) . str_repeat('*', strlen($value) - 8) . substr($value, -4)
                    : $value;
                $this->line("   ✅ {$var}: {$masked}");
            } else {
                $this->error("   ❌ {$var}: NO DEFINIDA");
            }
        }
        $this->newLine();

        $this->info('✨ Diagnóstico completado');
    }
}