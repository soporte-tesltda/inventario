<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use Aws\S3\S3Client;

class DiagnoseR2Auth extends Command
{
    protected $signature = 'app:diagnose-r2-auth';
    protected $description = 'Diagnose Cloudflare R2 authentication issues';

    public function handle()
    {
        $this->info('🔍 DIAGNOSING CLOUDFLARE R2 AUTHENTICATION');
        $this->newLine();

        // Test 1: Basic connection
        $this->info('1. TESTING BASIC CONNECTION:');
        try {
            $disk = Storage::disk('private');
            $files = $disk->files('products');
            $this->line("   ✅ Connection OK - Found " . count($files) . " files in products folder");
        } catch (\Exception $e) {
            $this->error("   ❌ Connection failed: " . $e->getMessage());
        }
        $this->newLine();

        // Test 2: Test S3 client directly
        $this->info('2. TESTING S3 CLIENT DIRECTLY:');
        try {
            $config = config('filesystems.disks.private');
            
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => $config['region'],
                'endpoint' => $config['endpoint'],
                'use_path_style_endpoint' => $config['use_path_style_endpoint'],
                'credentials' => [
                    'key' => $config['key'],
                    'secret' => $config['secret'],
                ],
            ]);

            // Try to list objects
            $result = $s3Client->listObjectsV2([
                'Bucket' => $config['bucket'],
                'Prefix' => 'products/',
                'MaxKeys' => 5
            ]);

            $this->line("   ✅ S3 Client OK - Found " . count($result['Contents'] ?? []) . " objects");
            
            // Test presigned URL generation
            if (!empty($result['Contents'])) {
                $firstObject = $result['Contents'][0]['Key'];
                $this->line("   🔍 Testing presigned URL for: {$firstObject}");
                
                $cmd = $s3Client->getCommand('GetObject', [
                    'Bucket' => $config['bucket'],
                    'Key' => $firstObject
                ]);
                
                $request = $s3Client->createPresignedRequest($cmd, '+1 hour');
                $presignedUrl = (string) $request->getUri();
                
                $this->line("   🔗 Presigned URL: " . substr($presignedUrl, 0, 100) . '...');
                
                // Test the presigned URL
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 10,
                        'method' => 'HEAD'
                    ]
                ]);
                
                $headers = @get_headers($presignedUrl, 1, $context);
                if ($headers && strpos($headers[0], '200') !== false) {
                    $this->line('   ✅ Presigned URL WORKS!');
                } else {
                    $this->error('   ❌ Presigned URL failed: ' . ($headers[0] ?? 'No response'));
                }
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ S3 Client failed: " . $e->getMessage());
        }
        $this->newLine();

        // Test 3: Check permissions
        $this->info('3. TESTING PERMISSIONS:');
        try {
            $disk = Storage::disk('private');
            
            // Test read
            $files = $disk->files('products');
            if (count($files) > 0) {
                $firstFile = $files[0];
                $exists = $disk->exists($firstFile);
                $this->line("   ✅ Read permission OK - File exists: " . ($exists ? 'Yes' : 'No'));
                
                // Test get file info
                $size = $disk->size($firstFile);
                $this->line("   ✅ File info OK - Size: {$size} bytes");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Permission test failed: " . $e->getMessage());
        }
        $this->newLine();

        // Test 4: Environment check
        $this->info('4. ENVIRONMENT CHECK:');
        $this->line('   🔧 Laravel Cloud Disk Config: ' . (env('LARAVEL_CLOUD_DISK_CONFIG') ? 'ENABLED' : 'DISABLED'));
        $this->line('   🔧 Filesystem Disk: ' . env('FILESYSTEM_DISK', 'local'));
        $this->line('   🔧 Filament Filesystem Disk: ' . env('FILAMENT_FILESYSTEM_DISK', 'public'));
        
        $config = config('filesystems.disks.private');
        $this->line('   🔧 Private Disk Endpoint: ' . $config['endpoint']);
        $this->line('   🔧 Private Disk Region: ' . $config['region']);
        $this->line('   🔧 Private Disk Use Path Style: ' . ($config['use_path_style_endpoint'] ? 'true' : 'false'));

        $this->newLine();
        $this->info('✨ Diagnosis complete');
    }
}