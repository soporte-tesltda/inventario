<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckCloudConfig extends Command
{
    protected $signature = 'app:check-cloud-config';
    protected $description = 'Check Laravel Cloud configuration for S3/Cloudflare R2';

    public function handle()
    {
        $this->info('🔍 VERIFICACIÓN CONFIGURACIÓN LARAVEL CLOUD');
        $this->newLine();

        // 1. Variables de entorno manuales
        $this->info('1. VARIABLES DE ENTORNO MANUALES:');
        $manualVars = [
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY', 
            'AWS_BUCKET',
            'AWS_DEFAULT_REGION',
            'AWS_ENDPOINT',
            'AWS_URL',
            'AWS_USE_PATH_STYLE_ENDPOINT'
        ];
        
        foreach ($manualVars as $var) {
            $value = env($var);
            if ($value) {
                $masked = $var === 'AWS_SECRET_ACCESS_KEY' 
                    ? substr($value, 0, 4) . str_repeat('*', max(0, strlen($value) - 8)) . substr($value, -4)
                    : $value;
                $this->line("   ✅ {$var}: {$masked}");
            } else {
                $this->error("   ❌ {$var}: NO DEFINIDA");
            }
        }
        $this->newLine();

        // 2. Configuración automática de Laravel Cloud
        $this->info('2. CONFIGURACIÓN AUTOMÁTICA LARAVEL CLOUD:');
        $cloudConfig = env('LARAVEL_CLOUD_DISK_CONFIG');
        if ($cloudConfig) {
            $this->line('   ✅ LARAVEL_CLOUD_DISK_CONFIG: PRESENTE');
            
            try {
                $config = json_decode($cloudConfig, true);
                if (is_array($config) && !empty($config)) {
                    $privateConfig = collect($config)->firstWhere('disk', 'private');
                    if ($privateConfig) {
                        $this->line('   📋 Configuración disco "private":');
                        foreach ($privateConfig as $key => $value) {
                            if ($key === 'access_key_secret') {
                                $value = substr($value, 0, 4) . str_repeat('*', max(0, strlen($value) - 8)) . substr($value, -4);
                            }
                            $this->line("      {$key}: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value));
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->error('   ❌ Error parseando JSON: ' . $e->getMessage());
            }
        } else {
            $this->error('   ❌ LARAVEL_CLOUD_DISK_CONFIG: NO PRESENTE');
        }
        $this->newLine();

        // 3. Configuración final del disco
        $this->info('3. CONFIGURACIÓN FINAL DISCO "private":');
        $diskConfig = config('filesystems.disks.private');
        foreach ($diskConfig as $key => $value) {
            if ($key === 'secret') {
                $value = substr($value, 0, 4) . str_repeat('*', max(0, strlen($value) - 8)) . substr($value, -4);
            }
            $this->line("   {$key}: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value));
        }
        $this->newLine();

        // 4. Recomendaciones
        $this->info('4. RECOMENDACIONES:');
        $region = $diskConfig['region'] ?? '';
        $pathStyle = $diskConfig['use_path_style_endpoint'] ?? false;
        
        if ($region === 'auto') {
            $this->warn('   ⚠️  Región "auto" puede causar problemas. Recomendado: us-east-1');
        }
        
        if (!$pathStyle) {
            $this->warn('   ⚠️  use_path_style_endpoint=false puede causar problemas con Cloudflare R2');
        }
        
        if ($region === 'us-east-1' && $pathStyle) {
            $this->info('   ✅ Configuración parece correcta para Cloudflare R2');
        }

        $this->newLine();
        $this->info('✨ Verificación completada');
    }
}