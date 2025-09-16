<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario administrador principal de TES LTDA
        User::firstOrCreate([
            'email' => 'admin@tes.com',
        ], [
            'name' => 'Administrador TES',
            'email' => 'admin@tes.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Usuario operador de inventario
        User::firstOrCreate([
            'email' => 'operador@tes.com',
        ], [
            'name' => 'Operador TES',
            'email' => 'operador@tes.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Usuario para pruebas de desarrollo
        User::firstOrCreate([
            'email' => 'test@test.com',
        ], [
            'name' => 'Usuario de Prueba',
            'email' => 'test@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Usuarios creados para TES LTDA');
    }
}
