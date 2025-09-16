<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class StandardizeProductImages extends Command
{
    protected $signature = 'products:standardize-images';
    protected $description = 'Estandarizar todas las rutas de imágenes para usar el prefijo products/';

    public function handle()
    {
        $this->info('Estandarizando rutas de imágenes de productos...');
        
        $publicDisk = Storage::disk('public');
        $products = Product::whereNotNull('image')->get();
        
        $moved = 0;
        $updated = 0;

        foreach ($products as $product) {
            $originalImage = $product->image;
            
            // Si la imagen ya tiene el prefijo products/, continuar
            if (str_starts_with($originalImage, 'products/')) {
                $this->line("✅ Producto {$product->id}: Ya estandarizada - {$originalImage}");
                continue;
            }

            // Si la imagen existe en el directorio raíz, moverla a products/
            if ($publicDisk->exists($originalImage)) {
                $newPath = 'products/' . basename($originalImage);
                
                // Crear directorio products si no existe
                if (!$publicDisk->exists('products')) {
                    $publicDisk->makeDirectory('products');
                }
                
                // Mover el archivo
                if ($publicDisk->move($originalImage, $newPath)) {
                    $product->image = $newPath;
                    $product->save();
                    $this->line("🔄 Producto {$product->id}: Movido {$originalImage} → {$newPath}");
                    $moved++;
                } else {
                    $this->error("❌ Error moviendo {$originalImage}");
                }
            } else {
                // Si no existe, simplemente actualizar la ruta (asumiendo que debería estar en products/)
                $newPath = 'products/' . basename($originalImage);
                $product->image = $newPath;
                $product->save();
                $this->line("🔄 Producto {$product->id}: Ruta actualizada {$originalImage} → {$newPath}");
                $updated++;
            }
        }

        $this->info("\nEstandarización completada:");
        $this->info("- Archivos movidos: {$moved}");
        $this->info("- Rutas actualizadas: {$updated}");
    }
}
