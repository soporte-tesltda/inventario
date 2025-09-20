<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Models\Product;

class PreCacheImages extends Command
{
    protected $signature = 'app:precache-images {--limit=50 : Number of images to precache}';
    protected $description = 'Pre-cache popular product images to avoid timeouts';

    public function handle()
    {
        $limit = $this->option('limit');
        
        $this->info("🚀 PRE-CACHING {$limit} PRODUCT IMAGES");
        $this->newLine();

        // Obtener productos con imágenes, ordenados por ID descendente (más recientes primero)
        $products = Product::whereNotNull('image')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        if ($products->isEmpty()) {
            $this->error('❌ No se encontraron productos con imágenes');
            return;
        }

        $cached = 0;
        $errors = 0;
        $skipped = 0;

        $progressBar = $this->output->createProgressBar($products->count());
        $progressBar->start();

        foreach ($products as $product) {
            $imagePath = $product->image;
            $cacheKey = 'image_cache_' . md5('products/' . $imagePath);
            
            // Si ya está en caché, saltar
            if (Cache::has($cacheKey)) {
                $skipped++;
                $progressBar->advance();
                continue;
            }

            try {
                // Descargar imagen desde R2
                $fileContent = Storage::disk('private')->get($imagePath);
                
                if ($fileContent) {
                    // Determinar tipo MIME
                    $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
                    $mimeType = match(strtolower($extension)) {
                        'jpg', 'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                        'webp' => 'image/webp',
                        'svg' => 'image/svg+xml',
                        default => 'application/octet-stream'
                    };

                    // Cachear por 24 horas
                    Cache::put($cacheKey, [
                        'content' => $fileContent,
                        'mime_type' => $mimeType,
                        'cached_at' => now(),
                        'precached' => true
                    ], 86400);

                    $cached++;
                } else {
                    $errors++;
                }
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("❌ Error cacheando {$imagePath}: " . $e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Mostrar resultados
        $this->info("✅ RESULTADOS DEL PRE-CACHING:");
        $this->line("   🎯 Total procesados: {$products->count()}");
        $this->line("   💾 Nuevas cacheadas: {$cached}");
        $this->line("   ⏭️  Ya en caché: {$skipped}");
        $this->line("   ❌ Errores: {$errors}");
        
        if ($cached > 0) {
            $this->newLine();
            $this->info("🚀 {$cached} imágenes pre-cacheadas exitosamente!");
            $this->line("   Las próximas visitas serán instantáneas para estas imágenes.");
        }

        // Mostrar estadísticas de caché
        $totalCacheKeys = 0;
        $cachePattern = 'image_cache_*';
        
        try {
            if (config('cache.default') === 'redis') {
                $redis = \Illuminate\Support\Facades\Redis::connection();
                $keys = $redis->keys('*image_cache_*');
                $totalCacheKeys = count($keys);
            }
        } catch (\Exception $e) {
            // Ignorar errores de Redis
        }

        if ($totalCacheKeys > 0) {
            $this->newLine();
            $this->info("📊 ESTADÍSTICAS GENERALES:");
            $this->line("   📦 Total imágenes en caché: {$totalCacheKeys}");
        }
    }
}