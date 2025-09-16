<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear rol de operador si no existe
        $operatorRole = Role::firstOrCreate(['name' => 'operador']);
        
        // Permisos para el rol de operador (solo gestión de inventario)
        $operatorPermissions = [
            // Productos
            'view_any_product',
            'view_product', 
            'create_product',
            'update_product',
            'delete_product',
            
            // Categorías de productos
            'view_any_product::category',
            'view_product::category',
            'create_product::category', 
            'update_product::category',
            'delete_product::category',
            
            // Proveedores
            'view_any_product::supplier',
            'view_product::supplier',
            'create_product::supplier',
            'update_product::supplier', 
            'delete_product::supplier',
            
            // Órdenes
            'view_any_order',
            'view_order',
            'create_order',
            'update_order',
            'delete_order',
        ];

        // Asignar permisos al rol de operador
        foreach($operatorPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if($permission) {
                $operatorRole->givePermissionTo($permission);
            }
        }

        // Asignar rol super_admin al administrador
        $adminUser = User::where('email', 'admin@tes.com')->first();
        if($adminUser) {
            $adminUser->assignRole('super_admin');
            $this->command->info('✅ Rol super_admin asignado a admin@tes.com');
        }

        // Asignar rol operador al usuario operador
        $operatorUser = User::where('email', 'operador@tes.com')->first();
        if($operatorUser) {
            $operatorUser->assignRole('operador');
            $this->command->info('✅ Rol operador asignado a operador@tes.com');
        }

        $this->command->info('✅ Roles asignados correctamente para TES LTDA');
    }
}
