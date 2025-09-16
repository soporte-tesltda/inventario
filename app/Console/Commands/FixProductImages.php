<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixProductImages extends Command
{
    protected $signature = 'products:fix-images';
    protected $description = 'Reparar rutas de imágenes de productos';

    public function handle()
    {
        $this->info('Reparando rutas de imágenes de productos...');
        
        // Obtener todos los archivos de imagen en el directorio products
        $publicDisk = Storage::disk('public');
        $productImages = collect($publicDisk->files())
            ->filter(fn($file) => preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file))
            ->concat(
                collect($publicDisk->files('products'))
                    ->filter(fn($file) => preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file))
            );
        
        $this->info('Archivos de imagen encontrados: ' . $productImages->count());
        
        $products = Product::whereNotNull('image')->get();
        $fixed = 0;
        $notFound = 0;
        
        foreach ($products as $product) {
            $originalImage = $product->image;
            $imageFixed = false;
            
            // Si la imagen ya tiene el prefijo products/, verificar si existe
            if (str_starts_with($originalImage, 'products/')) {
                if ($publicDisk->exists($originalImage)) {
                    continue; // La imagen ya está correcta
                }
                // Si no existe, intentar encontrarla sin el prefijo
                $imageWithoutPrefix = str_replace('products/', '', $originalImage);
                if ($publicDisk->exists($imageWithoutPrefix)) {
                    $product->image = $imageWithoutPrefix;
                    $product->save();
                    $this->line("✅ Producto {$product->id}: {$originalImage} → {$imageWithoutPrefix}");
                    $fixed++;
                    $imageFixed = true;
                }
            } else {
                // Si no tiene prefijo, verificar si existe tal como está
                if ($publicDisk->exists($originalImage)) {
                    continue; // La imagen ya está correcta
                }
                // Si no existe, intentar con el prefijo products/
                $imageWithPrefix = 'products/' . $originalImage;
                if ($publicDisk->exists($imageWithPrefix)) {
                    $product->image = $imageWithPrefix;
                    $product->save();
                    $this->line("✅ Producto {$product->id}: {$originalImage} → {$imageWithPrefix}");
                    $fixed++;
                    $imageFixed = true;
                }
            }
            
            if (!$imageFixed) {
                $this->error("❌ Producto {$product->id}: No se encontró la imagen '{$originalImage}'");
                $notFound++;
            }
        }
        
        $this->info("Reparación completada:");
        $this->info("- Imágenes reparadas: {$fixed}");
        $this->info("- Imágenes no encontradas: {$notFound}");
        
        // Mostrar archivos disponibles
        $this->info("\nArchivos de imagen disponibles:");
        foreach ($productImages->take(10) as $file) {
            $this->line("- {$file}");
        }
        if ($productImages->count() > 10) {
            $this->line("... y " . ($productImages->count() - 10) . " más");
        }
    }
}
