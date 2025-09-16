<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class CheckProductImages extends Command
{
    protected $signature = 'products:check-images';
    protected $description = 'Verificar el estado de las imágenes de productos';

    public function handle()
    {
        $this->info('Verificando imágenes de productos...');
        
        $products = Product::select('id', 'name', 'image')->limit(10)->get();
        
        $this->table(['ID', 'Nombre', 'Imagen', 'Archivo Existe'], 
            $products->map(function ($product) {
                $imageExists = $product->image ? file_exists(storage_path('app/public/' . $product->image)) : false;
                return [
                    $product->id,
                    $product->name,
                    $product->image ?? 'Sin imagen',
                    $imageExists ? 'Sí' : 'No'
                ];
            })
        );
        
        $this->info('Verificación completada.');
    }
}
