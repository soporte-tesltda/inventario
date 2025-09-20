<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestImageUrls extends Command
{
    protected $signature = 'images:test-urls';
    protected $description = 'Test image URL generation';

    public function handle()
    {
        $product = Product::whereNotNull('image')->first();
        
        if (!$product) {
            $this->error('No hay productos con imágenes');
            return;
        }

        $this->info('=== TESTING IMAGE URLS ===');
        $this->info('Product ID: ' . $product->id);
        $this->info('Product Name: ' . $product->name);
        $this->info('Image Field: ' . $product->image);
        
        $this->info('=== URL GENERATION TESTS ===');
        
        // Test 1: Direct Storage URL
        try {
            $directUrl = Storage::disk('private')->url($product->image);
            $this->info('✅ Direct Storage URL: ' . $directUrl);
        } catch (\Exception $e) {
            $this->error('❌ Direct Storage URL Error: ' . $e->getMessage());
        }
        
        // Test 2: Temporary URL
        try {
            $tempUrl = Storage::disk('private')->temporaryUrl($product->image, now()->addHours(1));
            $this->info('✅ Temporary URL: ' . $tempUrl);
        } catch (\Exception $e) {
            $this->error('❌ Temporary URL Error: ' . $e->getMessage());
        }
        
        // Test 3: Accessor URL
        try {
            $accessorUrl = $product->image_url;
            $this->info('✅ Accessor URL: ' . $accessorUrl);
        } catch (\Exception $e) {
            $this->error('❌ Accessor URL Error: ' . $e->getMessage());
        }
        
        // Test 4: HTTP Response
        $this->info('=== HTTP RESPONSE TEST ===');
        try {
            $testUrl = $product->image_url;
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'method' => 'HEAD'
                ]
            ]);
            
            $headers = @get_headers($testUrl, 1, $context);
            if ($headers && strpos($headers[0], '200') !== false) {
                $this->info('✅ HTTP Test: ' . $headers[0]);
            } else {
                $this->error('❌ HTTP Test Failed: ' . ($headers ? $headers[0] : 'No response'));
                if ($headers) {
                    $this->info('Full headers: ' . json_encode($headers, JSON_PRETTY_PRINT));
                }
            }
        } catch (\Exception $e) {
            $this->error('❌ HTTP Test Error: ' . $e->getMessage());
        }
        
        return 0;
    }
}