<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;

class MakeImagesPublic extends Command
{
    protected $signature = 'images:make-public {--dry-run : Show what would be done without making changes}';
    protected $description = 'Make all product images public in S3 storage';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('🔄 Making product images public...');

        // Get all products with images
        $products = Product::whereNotNull('image')->get();
        
        if ($products->isEmpty()) {
            $this->warn('No products with images found.');
            return 0;
        }

        $this->info("Found {$products->count()} products with images");

        $successCount = 0;
        $errorCount = 0;

        foreach ($products as $product) {
            try {
                $imagePath = $product->image;
                
                // Remove any existing domain from the path
                $r2BaseUrl = config('filesystems.disks.r2.url') . '/' . config('filesystems.disks.r2.bucket') . '/';
                $imagePath = str_replace([
                    $r2BaseUrl,
                    'products/'
                ], '', $imagePath);
                
                // Ensure path starts with products/
                if (!str_starts_with($imagePath, 'products/')) {
                    $imagePath = 'products/' . $imagePath;
                }

                if ($isDryRun) {
                    $this->line("Would make public: {$imagePath}");
                } else {
                    // Check if file exists in S3
                    if (Storage::disk('private')->exists($imagePath)) {
                        // Set file visibility to public
                        Storage::disk('private')->setVisibility($imagePath, 'public');
                        $this->line("✅ Made public: {$imagePath}");
                        $successCount++;
                    } else {
                        $this->warn("⚠️  File not found: {$imagePath}");
                        $errorCount++;
                    }
                }
            } catch (\Exception $e) {
                $this->error("❌ Error processing {$product->image}: " . $e->getMessage());
                $errorCount++;
            }
        }

        if (!$isDryRun) {
            $this->info("\n📊 Summary:");
            $this->info("✅ Successfully made public: {$successCount}");
            if ($errorCount > 0) {
                $this->warn("❌ Errors: {$errorCount}");
            }
        }

        return 0;
    }
}