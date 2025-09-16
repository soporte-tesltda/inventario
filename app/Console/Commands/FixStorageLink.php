<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixStorageLink extends Command
{
    protected $signature = 'storage:fix-link';
    protected $description = 'Reparar enlace simbólico de storage en Windows';

    public function handle()
    {
        $this->info('🔧 Reparando enlace simbólico de storage para Windows...');
        
        $publicStorage = public_path('storage');
        $appStorage = storage_path('app/public');
        
        // Verificar si ya existe
        if (is_link($publicStorage)) {
            $this->info('✅ El enlace simbólico ya existe.');
            return;
        }
        
        // Eliminar directorio si existe
        if (is_dir($publicStorage)) {
            $this->info('🗑️ Eliminando directorio storage existente...');
            $this->call('storage:unlink');
        }
        
        // Crear enlace simbólico usando mklink en Windows
        $this->info('🔗 Creando enlace simbólico...');
        
        $command = sprintf(
            'mklink /D "%s" "%s"',
            str_replace('/', '\\', $publicStorage),
            str_replace('/', '\\', $appStorage)
        );
        
        $result = shell_exec("cmd /c '$command' 2>&1");
        
        if (is_link($publicStorage)) {
            $this->info('✅ Enlace simbólico creado correctamente.');
            $this->info('   ' . $result);
            
            // Verificar que funciona
            $testFile = $appStorage . '/test-link.txt';
            file_put_contents($testFile, 'test');
            
            if (file_exists($publicStorage . '/test-link.txt')) {
                $this->info('✅ El enlace simbólico funciona correctamente.');
                unlink($testFile);
            } else {
                $this->error('❌ El enlace simbólico no funciona correctamente.');
            }
        } else {
            $this->error('❌ No se pudo crear el enlace simbólico.');
            $this->error('   ' . $result);
            $this->info('💡 Intenta ejecutar como administrador o usar: php artisan storage:link');
        }
    }
}
