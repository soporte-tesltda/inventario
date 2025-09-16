<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Categorías específicas para TES LTDA (empresa de impresoras y tóners)
        $categories = [
            // Hardware - Equipos físicos
            ['title' => 'Impresoras', 'slug' => 'impresoras', 'product_type' => 'hardware'],
            ['title' => 'Escáneres', 'slug' => 'escaneres', 'product_type' => 'hardware'],
            ['title' => 'Plotters', 'slug' => 'plotters', 'product_type' => 'hardware'],
            ['title' => 'Computadores de Escritorio', 'slug' => 'computadores-escritorio', 'product_type' => 'hardware'],
            
            // Consumibles - Productos que se agotan
            ['title' => 'Tóners Originales', 'slug' => 'toners-originales', 'product_type' => 'consumable'],
            ['title' => 'Tóners Genéricos', 'slug' => 'toners-genericos', 'product_type' => 'consumable'],
            ['title' => 'Tóners Remanufacturados', 'slug' => 'toners-remanufacturados', 'product_type' => 'consumable'],
            ['title' => 'Tintas', 'slug' => 'tintas', 'product_type' => 'consumable'],
            ['title' => 'Papel y Medios', 'slug' => 'papel-medios', 'product_type' => 'consumable'],
            
            // Accesorios
            ['title' => 'Cables y Conectores', 'slug' => 'cables-conectores', 'product_type' => 'accessory'],
            ['title' => 'Componentes y Repuestos', 'slug' => 'componentes-repuestos', 'product_type' => 'accessory'],
            ['title' => 'Memorias USB', 'slug' => 'memorias-usb', 'product_type' => 'accessory'],
    
        ];

        foreach ($categories as $category) {
            ProductCategory::firstOrCreate(
                ['slug' => $category['slug']], 
                $category
            );
        }

        $this->command->info('✅ Categorías de productos creadas para TES LTDA');
    }
}
