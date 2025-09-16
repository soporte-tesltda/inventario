<?php

namespace App\Console\Commands;

use App\Models\ProductCategory;
use Illuminate\Console\Command;

class UpdateComputerCategory extends Command
{
    protected $signature = 'categories:update-computers';
    protected $description = 'Actualizar categoría de computadores de escritorio a computadores';

    public function handle()
    {
        $this->info('🔄 Actualizando categoría de computadores...');
        
        // Buscar la categoría existente
        $category = ProductCategory::where('slug', 'computadores-escritorio')->first();
        
        if ($category) {
            $this->info("✅ Categoría encontrada: {$category->title} (slug: {$category->slug})");
            
            // Actualizar título y slug
            $category->title = 'Computadores';
            $category->slug = 'computadores';
            $category->save();
            
            $this->info("✅ Categoría actualizada a: {$category->title} (slug: {$category->slug})");
        } else {
            $this->info("⚠️ Categoría 'computadores-escritorio' no encontrada. Creando nueva...");
            
            ProductCategory::create([
                'title' => 'Computadores',
                'slug' => 'computadores',
                'product_type' => 'hardware'
            ]);
            
            $this->info("✅ Nueva categoría 'Computadores' creada");
        }
        
        // Listar todas las categorías para verificar
        $this->info("\n📋 Categorías actuales:");
        $categories = ProductCategory::all();
        foreach ($categories as $cat) {
            $this->line("  - {$cat->title} (slug: {$cat->slug}, tipo: {$cat->product_type})");
        }
    }
}
