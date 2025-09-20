<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\Http;

class TestImageProxy extends Command
{
    protected $signature = 'app:test-image-proxy';
    protected $description = 'Test the image proxy functionality';

    public function handle()
    {
        $this->info('🔍 TESTING IMAGE PROXY FUNCTIONALITY');
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

        // Test 1: URL del modelo (proxy)
        $this->info('1. URL DEL MODELO (PROXY):');
        $proxyUrl = $product->image_url;
        $this->line("   🔗 URL generada: {$proxyUrl}");
        
        // Test 2: Probar el controlador directamente
        $this->info('2. TEST CONTROLADOR DIRECTO:');
        try {
            $filename = basename($product->image);
            $controller = new \App\Http\Controllers\ImageProxyController();
            
            // Simular request
            $request = new \Illuminate\Http\Request();
            
            $this->line("   🔍 Probando archivo: {$filename}");
            
            // Test si el archivo existe en R2
            if (\Storage::disk('private')->exists($product->image)) {
                $this->line('   ✅ Archivo existe en Cloudflare R2');
                
                // Test obtener contenido
                $content = \Storage::disk('private')->get($product->image);
                $this->line('   ✅ Contenido obtenido - Tamaño: ' . strlen($content) . ' bytes');
                
                // Test type
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                $this->line("   🔧 Extensión: {$extension}");
                
            } else {
                $this->error('   ❌ Archivo NO existe en Cloudflare R2');
            }
            
        } catch (\Exception $e) {
            $this->error('   ❌ Error en controlador: ' . $e->getMessage());
        }
        $this->newLine();

        // Test 3: Verificar ruta
        $this->info('3. VERIFICAR RUTA:');
        $routes = \Route::getRoutes();
        $imageRoute = null;
        
        foreach ($routes as $route) {
            if (str_contains($route->getName() ?? '', 'image.proxy')) {
                $imageRoute = $route;
                break;
            }
        }
        
        if ($imageRoute) {
            $this->line('   ✅ Ruta registrada: ' . $imageRoute->uri());
            $this->line('   ✅ Nombre: ' . $imageRoute->getName());
            $this->line('   ✅ Métodos: ' . implode(', ', $imageRoute->methods()));
        } else {
            $this->error('   ❌ Ruta no encontrada');
        }

        $this->newLine();
        
        // Test 4: Simular request HTTP
        $this->info('4. SIMULACIÓN HTTP REQUEST:');
        $baseUrl = config('app.url');
        $fullUrl = str_replace('http://localhost', $baseUrl, $proxyUrl);
        $this->line("   🔗 URL completa: {$fullUrl}");
        
        $this->newLine();
        $this->info('✨ Test completado');
        $this->newLine();
        $this->info('💡 Para probar en navegador, visita:');
        $this->line("   {$proxyUrl}");
    }
}