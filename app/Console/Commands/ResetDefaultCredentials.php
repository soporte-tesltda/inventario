<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetDefaultCredentials extends Command
{
    protected $signature = 'security:reset-defaults';
    protected $description = 'Reset default credentials for security';

    public function handle()
    {
        $this->info('🔐 RESETTING DEFAULT CREDENTIALS FOR SECURITY');
        $this->newLine();

        // Buscar usuarios con credenciales por defecto
        $defaultUsers = User::whereIn('email', [
            'admin@example.com',
            'admin@admin.com',
            'test@test.com',
            'demo@demo.com'
        ])->get();

        if ($defaultUsers->isEmpty()) {
            $this->info('✅ No default credentials found. System is secure.');
            return;
        }

        foreach ($defaultUsers as $user) {
            $this->warn("⚠️  Found user with default email: {$user->email}");
            
            if ($this->confirm("Do you want to reset credentials for {$user->email}?")) {
                // Generar nueva contraseña segura
                $newPassword = Str::random(16);
                $user->password = Hash::make($newPassword);
                
                // Cambiar email si es necesario
                if ($user->email === 'admin@example.com') {
                    $newEmail = $this->ask('Enter new email address', 'admin@' . parse_url(config('app.url'), PHP_URL_HOST));
                    $user->email = $newEmail;
                }
                
                $user->save();
                
                $this->info("✅ User credentials updated:");
                $this->line("   📧 Email: {$user->email}");
                $this->line("   🔑 Password: {$newPassword}");
                $this->newLine();
                $this->warn("⚠️  IMPORTANT: Save these credentials in a secure location!");
                $this->newLine();
            }
        }

        // Verificar configuración de aplicación
        $this->info('🔍 CHECKING APPLICATION SECURITY:');
        
        if (config('app.debug') && config('app.env') === 'production') {
            $this->error('❌ DEBUG mode is ON in production! Set APP_DEBUG=false');
        } else {
            $this->info('✅ Debug mode correctly configured');
        }
        
        if (config('app.key') === 'base64:YOUR_APP_KEY_HERE') {
            $this->error('❌ Default APP_KEY detected! Run: php artisan key:generate');
        } else {
            $this->info('✅ Application key is set');
        }

        $this->newLine();
        $this->info('🔐 Security check completed!');
    }
}