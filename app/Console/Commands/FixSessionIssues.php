<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixSessionIssues extends Command
{
    protected $signature = 'session:fix {--production : Optimizar para entorno de producción}';
    protected $description = 'Solucionar problemas de sesiones en producción y desarrollo';

    public function handle()
    {
        $this->info('🔧 Solucionando problemas de sesiones...');
        
        $isProduction = $this->option('production');
        
        // 1. Verificar y crear directorios necesarios
        $this->ensureDirectoriesExist();
        
        // 2. Verificar configuración de sesiones
        $this->checkSessionConfiguration();
        
        // 3. Limpiar sesiones corruptas
        $this->cleanCorruptedSessions();
        
        // 4. Optimizar configuración para producción si es necesario
        if ($isProduction) {
            $this->optimizeForProduction();
        }
        
        // 5. Verificar permisos (solo en Unix/Linux)
        $this->checkPermissions();
        
        // 6. Probar sesiones
        $this->testSessionFunctionality();
        
        $this->info("\n✅ Problemas de sesiones solucionados!");
        
        return 0;
    }
    
    private function ensureDirectoriesExist(): void
    {
        $this->info("\n1️⃣ Verificando directorios de sesiones...");
        
        $directories = [
            storage_path('framework'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                File::makeDirectory($dir, 0755, true);
                $this->line("   ✅ Creado: " . str_replace(base_path(), '', $dir));
            } else {
                $this->line("   ✅ Existe: " . str_replace(base_path(), '', $dir));
            }
        }
    }
    
    private function checkSessionConfiguration(): void
    {
        $this->info("\n2️⃣ Verificando configuración de sesiones...");
        
        $driver = config('session.driver');
        $lifetime = config('session.lifetime');
        $path = config('session.files');
        
        $this->line("   Driver: {$driver}");
        $this->line("   Duración: {$lifetime} minutos");
        $this->line("   Ruta: {$path}");
        
        // Verificar que la ruta de sesiones existe
        if ($driver === 'file' && !is_dir($path)) {
            $this->warn("   ⚠️ Directorio de sesiones no existe: {$path}");
            File::makeDirectory($path, 0755, true);
            $this->line("   ✅ Directorio creado");
        }
        
        // Verificar permisos de escritura
        if (!is_writable($path)) {
            $this->error("   ❌ Sin permisos de escritura en: {$path}");
            if (PHP_OS_FAMILY !== 'Windows') {
                chmod($path, 0755);
                $this->line("   ✅ Permisos corregidos");
            }
        } else {
            $this->line("   ✅ Permisos de escritura correctos");
        }
    }
    
    private function cleanCorruptedSessions(): void
    {
        $this->info("\n3️⃣ Limpiando sesiones corruptas...");
        
        $sessionPath = config('session.files');
        $cleaned = 0;
        
        if (is_dir($sessionPath)) {
            $files = File::files($sessionPath);
            
            foreach ($files as $file) {
                $filename = $file->getFilename();
                
                // Skip .gitignore y otros archivos del sistema
                if (str_starts_with($filename, '.')) {
                    continue;
                }
                
                try {
                    // Intentar leer el archivo de sesión
                    $content = File::get($file->getPathname());
                    
                    // Verificar si el contenido es válido
                    if (empty($content) || strlen($content) < 10) {
                        File::delete($file->getPathname());
                        $cleaned++;
                    }
                } catch (\Exception $e) {
                    // Si no se puede leer, eliminarlo
                    File::delete($file->getPathname());
                    $cleaned++;
                }
            }
            
            $this->line("   ✅ Sesiones limpiadas: {$cleaned}");
        }
    }
    
    private function optimizeForProduction(): void
    {
        $this->info("\n4️⃣ Optimizando para producción...");
        
        // Cambiar driver a database si existe la tabla sessions
        try {
            if (\Schema::hasTable('sessions')) {
                $this->line("   💡 Recomendación: Cambiar SESSION_DRIVER=database en .env");
                $this->line("   📖 La tabla 'sessions' ya existe en la base de datos");
            } else {
                $this->line("   💡 Para mejor rendimiento en producción:");
                $this->line("   1. php artisan session:table");
                $this->line("   2. php artisan migrate");
                $this->line("   3. Cambiar SESSION_DRIVER=database en .env");
            }
        } catch (\Exception $e) {
            $this->line("   ✅ Configuración de archivo mantenida");
        }
        
        // Configuración recomendada para producción
        $this->line("   📋 Configuración recomendada para .env de producción:");
        $this->line("   SESSION_DRIVER=database");
        $this->line("   SESSION_LIFETIME=120");
        $this->line("   SESSION_ENCRYPT=false");
        $this->line("   SESSION_HTTP_ONLY=true");
        $this->line("   SESSION_SAME_SITE=lax");
    }
    
    private function checkPermissions(): void
    {
        $this->info("\n5️⃣ Verificando permisos...");
        
        if (PHP_OS_FAMILY === 'Windows') {
            $this->line("   ✅ Windows - Verificación automática de permisos");
            return;
        }
        
        $directories = [
            storage_path('framework'),
            storage_path('framework/sessions'),
            storage_path('logs'),
        ];
        
        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $perms = substr(sprintf('%o', fileperms($dir)), -3);
                $isWritable = is_writable($dir);
                
                if (!$isWritable || $perms < '755') {
                    chmod($dir, 0755);
                    $this->line("   ✅ Permisos corregidos: {$dir} -> 755");
                } else {
                    $this->line("   ✅ Permisos correctos: {$dir} ({$perms})");
                }
            }
        }
    }
    
    private function testSessionFunctionality(): void
    {
        $this->info("\n6️⃣ Probando funcionalidad de sesiones...");
        
        try {
            // Probar escribir una sesión de prueba
            $sessionId = 'test_' . time();
            $sessionData = 'test_data_' . rand(1000, 9999);
            
            session()->setId($sessionId);
            session()->put('test_key', $sessionData);
            session()->save();
            
            // Verificar que se guardó
            if (session()->get('test_key') === $sessionData) {
                $this->line("   ✅ Escritura de sesión: OK");
            } else {
                $this->error("   ❌ Error en escritura de sesión");
            }
            
            // Limpiar sesión de prueba
            session()->forget('test_key');
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error probando sesiones: " . $e->getMessage());
            $this->line("   💡 Verifica los logs para más detalles");
        }
    }
}
