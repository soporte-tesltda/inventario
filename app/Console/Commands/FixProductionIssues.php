<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixProductionIssues extends Command
{
    protected $signature = 'production:fix {--check-only : Solo verificar problemas sin solucionarlos}';
    protected $description = 'Solucionar problemas comunes de producción (sesiones, permisos, storage, etc.)';

    public function handle()
    {
        $this->info('🔧 Solucionando problemas comunes de producción...');
        
        $checkOnly = $this->option('check-only');
        
        if ($checkOnly) {
            $this->warn('⚠️ Modo solo verificación activado - no se realizarán cambios');
        }
        
        $issues = [];
        
        // 1. Verificar directorios de framework
        $issues = array_merge($issues, $this->checkAndFixDirectories($checkOnly));
        
        // 2. Verificar permisos
        $issues = array_merge($issues, $this->checkAndFixPermissions($checkOnly));
        
        // 3. Verificar configuración de producción
        $issues = array_merge($issues, $this->checkProductionConfig($checkOnly));
        
        // 4. Verificar storage y enlaces
        $issues = array_merge($issues, $this->checkStorageLinks($checkOnly));
        
        // 5. Limpiar caché problemático
        if (!$checkOnly) {
            $this->clearProblematicCache();
        }
        
        $this->displaySummary($issues, $checkOnly);
        
        return empty($issues) ? 0 : 1;
    }
    
    private function checkAndFixDirectories(bool $checkOnly): array
    {
        $this->info("\n1️⃣ Verificando directorios del framework...");
        
        $issues = [];
        $directories = [
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/framework/cache',
            'storage/framework/cache/data',
            'storage/app/public',
            'storage/app/public/products',
            'storage/logs',
            'bootstrap/cache',
        ];
        
        foreach ($directories as $dir) {
            $fullPath = base_path($dir);
            
            if (!is_dir($fullPath)) {
                $issues[] = "Directorio faltante: {$dir}";
                
                if (!$checkOnly) {
                    File::makeDirectory($fullPath, 0755, true);
                    $this->line("   ✅ Creado: {$dir}");
                } else {
                    $this->line("   ❌ Falta: {$dir}");
                }
            } else {
                $this->line("   ✅ Existe: {$dir}");
            }
        }
        
        return $issues;
    }
    
    private function checkAndFixPermissions(bool $checkOnly): array
    {
        $this->info("\n2️⃣ Verificando permisos...");
        
        $issues = [];
        
        // Solo en sistemas Unix/Linux
        if (PHP_OS_FAMILY !== 'Windows') {
            $directories = [
                'storage' => 0755,
                'storage/framework' => 0755,
                'storage/framework/sessions' => 0755,
                'storage/framework/views' => 0755,
                'storage/framework/cache' => 0755,
                'storage/app/public' => 0755,
                'storage/logs' => 0755,
                'bootstrap/cache' => 0755,
            ];
            
            foreach ($directories as $dir => $permission) {
                $fullPath = base_path($dir);
                
                if (is_dir($fullPath)) {
                    $currentPerms = substr(sprintf('%o', fileperms($fullPath)), -3);
                    $expectedPerms = substr(sprintf('%o', $permission), -3);
                    
                    if ($currentPerms !== $expectedPerms) {
                        $issues[] = "Permisos incorrectos en {$dir}: {$currentPerms} (esperado: {$expectedPerms})";
                        
                        if (!$checkOnly) {
                            chmod($fullPath, $permission);
                            $this->line("   ✅ Permisos corregidos: {$dir} -> {$expectedPerms}");
                        } else {
                            $this->line("   ❌ Permisos incorrectos: {$dir} ({$currentPerms})");
                        }
                    } else {
                        $this->line("   ✅ Permisos correctos: {$dir} ({$currentPerms})");
                    }
                }
            }
        } else {
            $this->line("   ✅ Windows - Verificación de permisos omitida");
        }
        
        return $issues;
    }
    
    private function checkProductionConfig(bool $checkOnly): array
    {
        $this->info("\n3️⃣ Verificando configuración de producción...");
        
        $issues = [];
        
        // Verificar configuración de sesiones
        $sessionDriver = config('session.driver');
        $sessionPath = config('session.files');
        
        $this->line("   Driver de sesiones: {$sessionDriver}");
        $this->line("   Ruta de sesiones: {$sessionPath}");
        
        if ($sessionDriver === 'file' && !is_dir($sessionPath)) {
            $issues[] = "Directorio de sesiones no existe: {$sessionPath}";
            
            if (!$checkOnly) {
                File::makeDirectory($sessionPath, 0755, true);
                $this->line("   ✅ Directorio de sesiones creado");
            } else {
                $this->line("   ❌ Directorio de sesiones faltante");
            }
        }
        
        // Verificar configuración de cache
        $cacheDriver = config('cache.default');
        $this->line("   Driver de caché: {$cacheDriver}");
        
        if ($cacheDriver === 'file') {
            $cachePath = config('cache.stores.file.path');
            if (!is_dir($cachePath)) {
                $issues[] = "Directorio de caché no existe: {$cachePath}";
                
                if (!$checkOnly) {
                    File::makeDirectory($cachePath, 0755, true);
                    $this->line("   ✅ Directorio de caché creado");
                } else {
                    $this->line("   ❌ Directorio de caché faltante");
                }
            }
        }
        
        return $issues;
    }
    
    private function checkStorageLinks(bool $checkOnly): array
    {
        $this->info("\n4️⃣ Verificando enlaces de almacenamiento...");
        
        $issues = [];
        
        $publicStoragePath = public_path('storage');
        
        if (!is_dir($publicStoragePath)) {
            $issues[] = "Enlace simbólico de storage no existe";
            
            if (!$checkOnly) {
                $this->call('storage:link');
                $this->line("   ✅ Enlace simbólico recreado");
            } else {
                $this->line("   ❌ Enlace simbólico faltante");
            }
        } else {
            $this->line("   ✅ Enlace simbólico existe");
        }
        
        return $issues;
    }
    
    private function clearProblematicCache(): void
    {
        $this->info("\n5️⃣ Limpiando caché problemático...");
        
        try {
            // Limpiar caché de configuración
            $this->call('config:clear');
            $this->line("   ✅ Caché de configuración limpiado");
            
            // Limpiar caché de rutas
            $this->call('route:clear');
            $this->line("   ✅ Caché de rutas limpiado");
            
            // Limpiar caché de vistas
            $this->call('view:clear');
            $this->line("   ✅ Caché de vistas limpiado");
            
            // Limpiar caché de aplicación (con manejo de errores)
            try {
                $this->call('cache:clear');
                $this->line("   ✅ Caché de aplicación limpiado");
            } catch (\Exception $e) {
                $this->line("   ⚠️ Error limpiando caché de aplicación: " . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error durante limpieza de caché: " . $e->getMessage());
        }
    }
    
    private function displaySummary(array $issues, bool $checkOnly): void
    {
        $this->info("\n" . str_repeat('=', 60));
        $this->info('📋 RESUMEN DE CORRECCIÓN DE PRODUCCIÓN');
        $this->info(str_repeat('=', 60));
        
        if (empty($issues)) {
            $this->info("🎉 ¡Todo está configurado correctamente!");
            $this->info("   ✅ No se encontraron problemas");
        } else {
            $this->error("❌ Se encontraron " . count($issues) . " problemas:");
            foreach ($issues as $issue) {
                $this->line("   • {$issue}");
            }
            
            if ($checkOnly) {
                $this->info("\n💡 Para solucionar los problemas ejecuta:");
                $this->info("   php artisan production:fix");
            } else {
                $this->info("\n✅ Problemas solucionados automáticamente");
            }
        }
        
        $this->info("\n🔧 Comandos adicionales recomendados:");
        $this->info("   php artisan config:cache");
        $this->info("   php artisan route:cache");
        $this->info("   php artisan storage:link");
        $this->info("   php artisan products:production-check");
    }
}
