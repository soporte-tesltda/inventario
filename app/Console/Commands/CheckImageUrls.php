<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CheckImageUrls extends Command
{
    protected $signature = 'images:check-urls';
    protected $description = 'Check image URLs generation';

    public function handle()
    {
        $product = Product::whereNotNull('image')->first();
        
        if (!$product) {
            $this->error('No hay productos con imágenes');
            return;
        }

        $this->info('=== INFORMACIÓN DEL PRODUCTO ===');
        $this->info('ID: ' . $product->id);
        $this->info('Nombre: ' . $product->name);
        $this->info('URL en BD: ' . $product->image);
        
        $this->info('=== URLS GENERADAS ===');
        $this->info('Storage URL: ' . Storage::disk('private')->url($product->image));
        
        $this->info('=== CONFIGURACIÓN ACTUAL ===');
        $this->info('FILAMENT_FILESYSTEM_DISK: ' . env('FILAMENT_FILESYSTEM_DISK'));
        $this->info('AWS_BUCKET: ' . env('AWS_BUCKET'));
        $this->info('AWS_URL: ' . env('AWS_URL'));
        
        $this->info('=== TEST DE ACCESIBILIDAD ===');
        $url = Storage::disk('private')->url($product->image);
        $this->info('Probando URL: ' . $url);
        
        // Intentar hacer una request HTTP
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'method' => 'HEAD'
                ]
            ]);
            
            $headers = @get_headers($url, 1, $context);
            if ($headers && strpos($headers[0], '200') !== false) {
                $this->info('✅ URL accesible - Status: ' . $headers[0]);
            } else {
                $this->error('❌ URL no accesible - Status: ' . ($headers ? $headers[0] : 'Sin respuesta'));
            }
        } catch (\Exception $e) {
            $this->error('❌ Error al acceder: ' . $e->getMessage());
        }
        
        return 0;
    }
}
