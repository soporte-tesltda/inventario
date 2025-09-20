<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CleanImageUrls extends Command
{
    protected $signature = 'app:clean-image-urls';
    protected $description = 'Clean image URLs to store only relative paths instead of full URLs';

    public function handle()
    {
        $this->info('🧹 LIMPIANDO URLs DE IMÁGENES EN BASE DE DATOS');
        $this->newLine();

        // Buscar productos con URLs completas
        $productsWithFullUrls = Product::where('image', 'LIKE', 'https://%')->get();
        
        $this->info("📊 Productos encontrados con URLs completas: {$productsWithFullUrls->count()}");
        
        if ($productsWithFullUrls->count() === 0) {
            $this->info('✅ No hay URLs que limpiar');
            return;
        }

        $this->newLine();
        $this->info('🔄 Procesando productos...');

        $updated = 0;
        $errors = 0;

        foreach ($productsWithFullUrls as $product) {
            try {
                $oldUrl = $product->image;
                
                // Extraer solo la parte del archivo desde 'products/'
                if (preg_match('/products\/([^\/]+)$/', $oldUrl, $matches)) {
                    $newPath = 'products/' . $matches[1];
                    
                    $product->update(['image' => $newPath]);
                    
                    $this->line("   ✅ {$product->name}");
                    $this->line("      Antes: {$oldUrl}");
                    $this->line("      Después: {$newPath}");
                    $this->newLine();
                    
                    $updated++;
                } else {
                    $this->warn("   ⚠️  No se pudo procesar: {$product->name} - {$oldUrl}");
                    $errors++;
                }
                
            } catch (\Exception $e) {
                $this->error("   ❌ Error procesando {$product->name}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->newLine();
        $this->info("📈 RESUMEN:");
        $this->line("   ✅ Productos actualizados: {$updated}");
        $this->line("   ❌ Errores: {$errors}");
        $this->newLine();
        
        if ($updated > 0) {
            $this->info('🎉 ¡Limpieza completada! Las imágenes deberían verse correctamente ahora.');
        }
    }
}