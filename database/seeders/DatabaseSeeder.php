<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutar seeders en orden de dependencias
        $this->call([
            UserSeeder::class,                // Usuarios del sistema
            ShieldSeeder::class,              // Roles y permisos primero
            RoleAssignmentSeeder::class,      // Asignar roles a usuarios
            ProductCategorySeeder::class,     // Categorías de productos
            ProductSupplierSeeder::class,     // Proveedores de productos  
            ProductSeeder::class,             // Productos (depende de categorías y proveedores)
            ContractSeeder::class,            // Contratos (depende de productos)
            OrderSeeder::class,               // Órdenes (depende de productos)
        ]);

        $this->command->info('✅ Base de datos poblada exitosamente con datos de TES LTDA');
    }
}
