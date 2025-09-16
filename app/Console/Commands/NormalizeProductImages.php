<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class NormalizeProductImages extends Command
{
    protected $signature = 'products:normalize-images';
    protected $description = 'Normalizar todas las rutas de imágenes de productos';

    public function handle()
    {
        $this->info('Normalizando rutas de imágenes de productos...');
        
        $publicDisk = Storage::disk('public');
        $products = Product::whereNotNull('image')->get();
        
        $normalized = 0;
        $removed = 0;
        $skipped = 0;

        foreach ($products as $product) {
            $originalImage = $product->image;
            $imageFound = false;
            $newImagePath = null;

            // Verificar si la imagen existe tal como está almacenada
            if ($publicDisk->exists($originalImage)) {
                $this->line("✅ Producto {$product->id}: Imagen OK - {$originalImage}");
                $skipped++;
                continue;
            }

            // Intentar diferentes variaciones de la ruta
            $possiblePaths = [
                $originalImage,
                'products/' . $originalImage,
                str_replace('products/', '', $originalImage),
                basename($originalImage), // Solo el nombre del archivo
            ];

            foreach ($possiblePaths as $path) {
                if ($publicDisk->exists($path)) {
                    $newImagePath = $path;
                    $imageFound = true;
                    break;
                }
            }

            if ($imageFound && $newImagePath !== $originalImage) {
                $product->image = $newImagePath;
                $product->save();
                $this->line("🔄 Producto {$product->id}: {$originalImage} → {$newImagePath}");
                $normalized++;
            } elseif (!$imageFound) {
                // Si no se encuentra la imagen, establecer como null
                $product->image = null;
                $product->save();
                $this->error("❌ Producto {$product->id}: Imagen no encontrada, eliminada referencia - {$originalImage}");
                $removed++;
            } else {
                $this->line("✅ Producto {$product->id}: Imagen OK - {$originalImage}");
                $skipped++;
            }
        }

        $this->info("\nNormalización completada:");
        $this->info("- Rutas normalizadas: {$normalized}");
        $this->info("- Referencias eliminadas: {$removed}");
        $this->info("- Imágenes ya correctas: {$skipped}");

        // Verificar productos sin imagen
        $withoutImage = Product::whereNull('image')->count();
        $this->info("- Productos sin imagen: {$withoutImage}");
    }
}
