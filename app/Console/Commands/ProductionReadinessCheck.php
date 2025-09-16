<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ProductionReadinessCheck extends Command
{
    protected $signature = 'system:production-ready';
    protected $description = 'Verificar que el sistema esté listo para producción (incluyendo imágenes)';

    public function handle()
    {
        $this->info('🔍 Verificando que el sistema esté listo para producción...');
          $checks = [
            'environment' => $this->checkEnvironment(),
            'database' => $this->checkDatabase(),
            'sessions' => $this->checkSessions(),
            'storage' => $this->checkStorage(),
            'images' => $this->checkImages(),
            'permissions' => $this->checkPermissions(),
            'optimization' => $this->checkOptimization(),
        ];
        
        $this->displayResults($checks);
        
        return $this->allChecksPassed($checks) ? 0 : 1;
    }
    
    private function checkEnvironment(): array
    {
        $this->info("\n1️⃣ Verificando configuración de entorno...");
        
        $checks = [];
        
        // APP_ENV
        $checks['app_env'] = [
            'status' => config('app.env') === 'production',
            'message' => config('app.env') === 'production' ? 
                'APP_ENV configurado correctamente' : 
                'APP_ENV debe ser "production"',
            'critical' => true
        ];
        
        // APP_DEBUG
        $checks['app_debug'] = [
            'status' => !config('app.debug'),
            'message' => !config('app.debug') ? 
                'APP_DEBUG deshabilitado correctamente' : 
                'APP_DEBUG debe ser false en producción',
            'critical' => true
        ];
        
        // APP_URL
        $appUrl = config('app.url');
        $isLocalhost = str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1');
        $checks['app_url'] = [
            'status' => !$isLocalhost,
            'message' => !$isLocalhost ? 
                'APP_URL configurada con dominio real' : 
                'APP_URL debe ser el dominio real, no localhost',
            'critical' => true
        ];
        
        return $checks;
    }
    
    private function checkDatabase(): array
    {
        $this->info("\n2️⃣ Verificando base de datos...");
        
        $checks = [];
        
        try {
            \DB::connection()->getPdo();
            $checks['connection'] = [
                'status' => true,
                'message' => 'Conexión a base de datos exitosa',
                'critical' => true
            ];
            
            // Verificar que existan tablas principales
            $tables = ['products', 'product_categories', 'users'];
            $existingTables = \DB::select("SHOW TABLES");
            $tableNames = array_map(function($table) {
                return array_values((array)$table)[0];
            }, $existingTables);
            
            $missingTables = array_diff($tables, $tableNames);
            $checks['tables'] = [
                'status' => empty($missingTables),
                'message' => empty($missingTables) ? 
                    'Todas las tablas principales existen' : 
                    'Faltan tablas: ' . implode(', ', $missingTables),
                'critical' => true
            ];
            
        } catch (\Exception $e) {
            $checks['connection'] = [
                'status' => false,
                'message' => 'Error de conexión a base de datos: ' . $e->getMessage(),
                'critical' => true
            ];
        }
          return $checks;
    }
    
    private function checkSessions(): array
    {
        $this->info("\n3️⃣ Verificando configuración de sesiones...");
        
        $checks = [];
        
        // Verificar driver de sesiones
        $sessionDriver = config('session.driver');
        $checks['session_driver'] = [
            'status' => true, // Informativo
            'message' => "Driver de sesiones: {$sessionDriver}",
            'critical' => false
        ];
        
        // Verificar configuración específica por driver
        if ($sessionDriver === 'file') {
            $sessionPath = config('session.files');
            $sessionDirExists = is_dir($sessionPath);
            $sessionWritable = $sessionDirExists ? is_writable($sessionPath) : false;
            
            $checks['session_directory'] = [
                'status' => $sessionDirExists && $sessionWritable,
                'message' => $sessionDirExists && $sessionWritable ?
                    'Directorio de sesiones accesible y escribible' :
                    'Directorio de sesiones no accesible - ejecutar: php artisan session:fix',
                'critical' => true
            ];
        } elseif ($sessionDriver === 'database') {
            try {
                $sessionsTableExists = \Schema::hasTable('sessions');
                $checks['sessions_table'] = [
                    'status' => $sessionsTableExists,
                    'message' => $sessionsTableExists ?
                        'Tabla de sesiones existe en base de datos' :
                        'Tabla de sesiones no existe - ejecutar: php artisan session:table && php artisan migrate',
                    'critical' => true
                ];
            } catch (\Exception $e) {
                $checks['sessions_table'] = [
                    'status' => false,
                    'message' => 'Error verificando tabla de sesiones: ' . $e->getMessage(),
                    'critical' => true
                ];
            }
        }
        
        // Recomendación para producción
        if (config('app.env') === 'production' && $sessionDriver === 'file') {
            $checks['session_recommendation'] = [
                'status' => false,
                'message' => 'Recomendado: Usar SESSION_DRIVER=database en producción',
                'critical' => false
            ];
        }
        
        return $checks;
    }
    
    private function checkStorage(): array
    {
        $this->info("\n4️⃣ Verificando configuración de almacenamiento...");
        
        $checks = [];
        
        // Verificar enlace simbólico
        $publicStoragePath = public_path('storage');
        $checks['symlink'] = [
            'status' => is_dir($publicStoragePath),
            'message' => is_dir($publicStoragePath) ? 
                'Enlace simbólico storage existe' : 
                'Enlace simbólico storage no existe - ejecutar: php artisan storage:link',
            'critical' => true
        ];
        
        // Verificar directorio products
        $productsPath = $publicStoragePath . DIRECTORY_SEPARATOR . 'products';
        $checks['products_dir'] = [
            'status' => is_dir($productsPath),
            'message' => is_dir($productsPath) ? 
                'Directorio products accesible' : 
                'Directorio products no accesible',
            'critical' => true
        ];
        
        // Verificar permisos de escritura
        $storageAppPublic = storage_path('app/public');
        $checks['write_permissions'] = [
            'status' => is_writable($storageAppPublic),
            'message' => is_writable($storageAppPublic) ? 
                'Permisos de escritura correctos' : 
                'Sin permisos de escritura en storage/app/public',
            'critical' => true
        ];
        
        return $checks;
    }
      private function checkImages(): array
    {
        $this->info("\n5️⃣ Verificando imágenes de productos...");
        
        $checks = [];
        
        // Contar productos con imágenes
        $productsWithImages = Product::whereNotNull('image')->count();
        $totalProducts = Product::count();
        
        $checks['products_with_images'] = [
            'status' => true, // Informativo
            'message' => "Productos con imágenes: {$productsWithImages}/{$totalProducts}",
            'critical' => false
        ];
        
        // Verificar imágenes accesibles
        $sampleProduct = Product::whereNotNull('image')->first();
        if ($sampleProduct) {
            $imageExists = Storage::disk('public')->exists($sampleProduct->image);
            $checks['sample_image'] = [
                'status' => $imageExists,
                'message' => $imageExists ? 
                    'Imágenes de muestra accesibles' : 
                    'Imágenes no accesibles - verificar configuración',
                'critical' => true
            ];
        }
        
        return $checks;
    }
      private function checkPermissions(): array
    {
        $this->info("\n6️⃣ Verificando permisos...");
        
        $checks = [];
        
        // Solo verificar en sistemas Unix/Linux
        if (PHP_OS_FAMILY !== 'Windows') {
            $directories = [
                'storage/' => storage_path(),
                'storage/app/public/' => storage_path('app/public'),
                'public/storage/' => public_path('storage'),
            ];
            
            foreach ($directories as $name => $path) {
                if (is_dir($path)) {
                    $perms = substr(sprintf('%o', fileperms($path)), -3);
                    $isWritable = is_writable($path);
                    
                    $checks["perms_{$name}"] = [
                        'status' => $isWritable,
                        'message' => "{$name}: {$perms} " . ($isWritable ? '(Escribible)' : '(Sin escritura)'),
                        'critical' => $isWritable
                    ];
                }
            }
        } else {
            $checks['permissions_windows'] = [
                'status' => true,
                'message' => 'Windows - Verificación de permisos omitida',
                'critical' => false
            ];
        }
        
        return $checks;
    }
      private function checkOptimization(): array
    {
        $this->info("\n7️⃣ Verificando optimizaciones...");
        
        $checks = [];
        
        // Verificar caché de configuración
        $configCached = file_exists(base_path('bootstrap/cache/config.php'));
        $checks['config_cache'] = [
            'status' => $configCached,
            'message' => $configCached ? 
                'Configuración en caché' : 
                'Configuración no cacheada - ejecutar: php artisan config:cache',
            'critical' => false
        ];
        
        // Verificar caché de rutas
        $routesCached = file_exists(base_path('bootstrap/cache/routes-v7.php'));
        $checks['routes_cache'] = [
            'status' => $routesCached,
            'message' => $routesCached ? 
                'Rutas en caché' : 
                'Rutas no cacheadas - ejecutar: php artisan route:cache',
            'critical' => false
        ];
        
        return $checks;
    }
    
    private function displayResults(array $allChecks): void
    {
        $this->info("\n" . str_repeat('=', 60));
        $this->info('📋 RESUMEN DE VERIFICACIÓN DE PRODUCCIÓN');
        $this->info(str_repeat('=', 60));
        
        $criticalIssues = 0;
        $warnings = 0;
        $totalChecks = 0;
        
        foreach ($allChecks as $category => $checks) {
            $this->info("\n" . strtoupper(str_replace('_', ' ', $category)) . ":");
            
            foreach ($checks as $check) {
                $totalChecks++;
                $icon = $check['status'] ? '✅' : ($check['critical'] ? '❌' : '⚠️');
                $this->line("   {$icon} {$check['message']}");
                
                if (!$check['status']) {
                    if ($check['critical']) {
                        $criticalIssues++;
                    } else {
                        $warnings++;
                    }
                }
            }
        }
        
        $this->info("\n" . str_repeat('=', 60));
        $this->info('📊 ESTADÍSTICAS:');
        $this->info("   Total verificaciones: {$totalChecks}");
        $this->info("   Problemas críticos: {$criticalIssues}");
        $this->info("   Advertencias: {$warnings}");
        
        if ($criticalIssues === 0) {
            $this->info("\n🎉 ¡Sistema listo para producción!");
            $this->info("   ✅ Todas las verificaciones críticas pasaron");
            if ($warnings > 0) {
                $this->warn("   ⚠️ Hay {$warnings} optimizaciones recomendadas");
            }
        } else {
            $this->error("\n❌ Sistema NO listo para producción");
            $this->error("   Se encontraron {$criticalIssues} problemas críticos");
            $this->info("   Soluciona los problemas marcados con ❌ antes de desplegar");
        }
        
        $this->info("\n📖 Para más detalles ver: PRODUCCION-IMAGENES.md");
    }
    
    private function allChecksPassed(array $allChecks): bool
    {
        foreach ($allChecks as $checks) {
            foreach ($checks as $check) {
                if (!$check['status'] && $check['critical']) {
                    return false;
                }
            }
        }
        return true;
    }
}
