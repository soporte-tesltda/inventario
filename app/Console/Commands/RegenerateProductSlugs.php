<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Console\Command;

class RegenerateProductSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slugs:regenerate {--type=all : Tipo de slugs a regenerar (products, categories, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerar slugs únicos para productos y/o categorías existentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');

        switch ($type) {
            case 'products':
                $this->regenerateProductSlugs();
                break;
            case 'categories':
                $this->regenerateCategorySlugs();
                break;
            case 'all':
            default:
                $this->regenerateProductSlugs();
                $this->regenerateCategorySlugs();
                break;
        }

        $this->info('✅ Regeneración de slugs completada.');
    }

    private function regenerateProductSlugs(): void
    {
        $this->info('🔄 Regenerando slugs de productos...');
        
        $products = Product::withTrashed()->get();
        $progressBar = $this->output->createProgressBar($products->count());
        $updated = 0;

        foreach ($products as $product) {
            $newSlug = Product::generateUniqueSlug($product->name, $product->id);
            
            if ($newSlug !== $product->slug) {
                $product->slug = $newSlug;
                $product->save();
                $updated++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("📦 Productos procesados: {$products->count()}");
        $this->info("✏️ Slugs actualizados: {$updated}");
    }

    private function regenerateCategorySlugs(): void
    {
        $this->info('🔄 Regenerando slugs de categorías...');
        
        $categories = ProductCategory::withTrashed()->get();
        $progressBar = $this->output->createProgressBar($categories->count());
        $updated = 0;

        foreach ($categories as $category) {
            $newSlug = ProductCategory::generateUniqueSlug($category->title, $category->id);
            
            if ($newSlug !== $category->slug) {
                $category->slug = $newSlug;
                $category->save();
                $updated++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("📂 Categorías procesadas: {$categories->count()}");
        $this->info("✏️ Slugs actualizados: {$updated}");
    }
}
