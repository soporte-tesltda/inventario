<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateProductPlaceholders extends Command
{
    protected $signature = 'products:generate-placeholders';
    protected $description = 'Generar imágenes placeholder para productos sin imagen';

    public function handle()
    {
        $this->info('Generando imágenes placeholder para productos sin imagen...');
        
        $publicDisk = Storage::disk('public');
        $productsWithoutImage = Product::whereNull('image')->get();
        
        if ($productsWithoutImage->isEmpty()) {
            $this->info('Todos los productos ya tienen imagen asignada.');
            return;
        }

        // Crear directorio products si no existe
        if (!$publicDisk->exists('products')) {
            $publicDisk->makeDirectory('products');
        }

        $generated = 0;

        foreach ($productsWithoutImage as $product) {
            // Generar un SVG placeholder personalizado para cada producto
            $svg = $this->generateProductSvg($product);
            $filename = 'placeholder-' . $product->slug . '.svg';
            $path = 'products/' . $filename;

            // Guardar el SVG
            $publicDisk->put($path, $svg);

            // Actualizar el producto
            $product->image = $path;
            $product->save();

            $this->line("✅ Producto {$product->id}: Placeholder generado - {$path}");
            $generated++;
        }

        $this->info("\nGeneración completada:");
        $this->info("- Placeholders generados: {$generated}");
    }

    private function generateProductSvg(Product $product): string
    {
        $name = substr($product->name, 0, 30) . (strlen($product->name) > 30 ? '...' : '');
        $category = $product->category ? $product->category->title : 'Sin categoría';
        
        // Colores basados en la categoría
        $colors = [
            'Impresoras' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'icon' => '#3b82f6'],
            'Tóneres' => ['bg' => '#fef3c7', 'text' => '#92400e', 'icon' => '#f59e0b'],
            'Computadores de Escritorio' => ['bg' => '#d1fae5', 'text' => '#065f46', 'icon' => '#10b981'],
            'default' => ['bg' => '#f3f4f6', 'text' => '#6b7280', 'icon' => '#9ca3af']
        ];

        $color = $colors[$category] ?? $colors['default'];

        return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg">
  <rect width="400" height="400" fill="' . $color['bg'] . '"/>
  
  <!-- Icono central -->
  <rect x="150" y="120" width="100" height="80" rx="8" fill="' . $color['icon'] . '" opacity="0.3"/>
  <rect x="160" y="130" width="80" height="60" rx="4" fill="' . $color['icon'] . '" opacity="0.6"/>
  
  <!-- Texto del producto -->
  <text x="200" y="230" text-anchor="middle" dominant-baseline="middle" fill="' . $color['text'] . '" font-family="Arial, sans-serif" font-size="16" font-weight="bold">
    ' . htmlspecialchars($name) . '
  </text>
  
  <!-- Categoría -->
  <text x="200" y="250" text-anchor="middle" dominant-baseline="middle" fill="' . $color['text'] . '" font-family="Arial, sans-serif" font-size="12" opacity="0.7">
    ' . htmlspecialchars($category) . '
  </text>
  
  <!-- ID del producto -->
  <text x="200" y="320" text-anchor="middle" dominant-baseline="middle" fill="' . $color['text'] . '" font-family="Arial, sans-serif" font-size="10" opacity="0.5">
    ID: ' . $product->id . '
  </text>
</svg>';
    }
}
