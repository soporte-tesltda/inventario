<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;

class TestSignedUrls extends Command
{
    protected $signature = 'app:test-signed-urls';
    protected $description = 'Test signed URL generation for Cloudflare R2';

    public function handle()
    {
        $this->info('🔍 TESTING SIGNED URLs FOR CLOUDFLARE R2');
        $this->newLine();

        // Buscar un producto con imagen
        $product = Product::whereNotNull('image')->first();
        
        if (!$product) {
            $this->error('❌ No se encontraron productos con imágenes');
            return;
        }

        $this->info("📦 Producto de prueba: {$product->name}");
        $this->line("🖼️  Imagen almacenada: {$product->image}");
        $this->newLine();

        // Test 1: URL firmada directa de Storage
        $this->info('1. TEST URL FIRMADA DIRECTA:');
        try {
            $signedUrl = Storage::disk('private')->temporaryUrl($product->image, now()->addHours(1));
            $this->line("   ✅ URL generada: {$signedUrl}");
            
            // Test HTTP de la URL firmada
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'method' => 'HEAD'
                ]
            ]);
            
            $headers = @get_headers($signedUrl, 1, $context);
            if ($headers && strpos($headers[0], '200') !== false) {
                $this->line('   ✅ URL firmada ACCESIBLE (HTTP 200)');
            } else {
                $this->error('   ❌ URL firmada NO accesible: ' . ($headers[0] ?? 'Sin respuesta'));
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Error generando URL firmada: ' . $e->getMessage());
        }
        $this->newLine();

        // Test 2: URL desde accessor del modelo
        $this->info('2. TEST ACCESSOR DEL MODELO:');
        try {
            $modelUrl = $product->image_url;
            $this->line("   ✅ URL del modelo: {$modelUrl}");
            
            // Test HTTP de la URL del modelo
            $headers = @get_headers($modelUrl, 1, $context);
            if ($headers && strpos($headers[0], '200') !== false) {
                $this->line('   ✅ URL del modelo ACCESIBLE (HTTP 200)');
            } else {
                $this->error('   ❌ URL del modelo NO accesible: ' . ($headers[0] ?? 'Sin respuesta'));
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Error obteniendo URL del modelo: ' . $e->getMessage());
        }
        $this->newLine();

        // Test 3: Verificar configuración
        $this->info('3. VERIFICACIÓN CONFIGURACIÓN:');
        $diskConfig = config('filesystems.disks.private');
        $this->line('   🔧 Driver: ' . $diskConfig['driver']);
        $this->line('   🗝️  Key: ' . substr($diskConfig['key'], 0, 8) . '...');
        $this->line('   🪣 Bucket: ' . $diskConfig['bucket']);
        $this->line('   🌐 Endpoint: ' . $diskConfig['endpoint']);
        $this->line('   📍 Region: ' . $diskConfig['region']);
        $this->line('   🔗 Path Style: ' . ($diskConfig['use_path_style_endpoint'] ? 'true' : 'false'));
        
        if (isset($diskConfig['temporary_url_timeout'])) {
            $this->line('   ⏱️  Timeout: ' . $diskConfig['temporary_url_timeout'] . ' segundos');
        }

        $this->newLine();
        $this->info('✨ Test completado');
    }
}