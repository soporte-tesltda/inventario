<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class MigrateProductImagesToS3 extends Command
{
    protected $signature = 'images:migrate-to-s3 {--dry-run} {--batch=100}';
    protected $description = 'Migrate product image URLs from local storage to S3 bucket';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch');

        $this->info('🚀 Migración de imágenes de productos a S3 (Cloudflare R2)');
        $this->info('Bucket: ' . env('AWS_BUCKET'));
        $this->info('Endpoint: ' . env('AWS_ENDPOINT'));
        
        if ($dryRun) {
            $this->warn('⚠️ MODO DRY-RUN - No se realizarán cambios reales');
        }

        // Obtener productos con imágenes
        $products = Product::whereNotNull('image')->get();
        $this->info("📊 Total de productos con imágenes: " . $products->count());

        $updated = 0;
        $errors = 0;
        $skipped = 0;

        $progressBar = $this->output->createProgressBar($products->count());
        $progressBar->start();

        foreach ($products->chunk($batchSize) as $batch) {
            foreach ($batch as $product) {
                try {
                    $currentImage = $product->image;
                    
                    // Verificar si ya está usando S3
                    if (str_contains($currentImage, env('AWS_ENDPOINT'))) {
                        $skipped++;
                        $progressBar->advance();
                        continue;
                    }

                    // Extraer solo el nombre del archivo
                    $imageName = basename($currentImage);
                    
                    // Construir nueva URL S3
                    $newImageUrl = env('AWS_ENDPOINT') . '/' . env('AWS_BUCKET') . '/products/' . $imageName;

                    if (!$dryRun) {
                        $product->update(['image' => $newImageUrl]);
                    }

                    $updated++;

                } catch (\Exception $e) {
                    $errors++;
                    $this->error("Error procesando producto {$product->id}: " . $e->getMessage());
                }

                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Mostrar resumen
        $this->info('📋 Resumen de migración:');
        $this->info("✅ Productos actualizados: {$updated}");
        $this->info("⏭️ Ya estaban en S3: {$skipped}");
        $this->info("❌ Errores: {$errors}");

        if ($dryRun) {
            $this->warn('⚠️ Para aplicar los cambios, ejecuta sin --dry-run');
        } else {
            $this->info('🎉 Migración completada exitosamente!');
        }

        // Mostrar ejemplos de URLs
        if ($updated > 0) {
            $this->info("\n📝 Ejemplos de URLs actualizadas:");
            $sampleProducts = Product::whereNotNull('image')
                ->where('image', 'like', '%' . env('AWS_ENDPOINT') . '%')
                ->limit(3)
                ->get();

            foreach ($sampleProducts as $sample) {
                $this->line("  - {$sample->name}: {$sample->image}");
            }
        }

        return $errors > 0 ? 1 : 0;
    }
}