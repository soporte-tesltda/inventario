<?php

namespace Database\Seeders;

use App\Models\ProductSupplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Proveedores reales para TES LTDA
        $suppliers = [
            [
                'name' => 'TES LTDA',
                'email' => 'tesltda@gmail.com',
                'phone' => '3147908909',
                'category_id' => 1, // Proveedor principal
            ],
            [
                'name' => 'HP Colombia',
                'email' => 'ventas@hp.com.co',
                'phone' => '+57 1 234 5678',
                'category_id' => 1,
            ],
            [
                'name' => 'Canon Colombia',
                'email' => 'soporte@canon.com.co',
                'phone' => '+57 1 345 6789',
                'category_id' => 1,
            ],
            [
                'name' => 'Brother Colombia',
                'email' => 'info@brother.com.co',
                'phone' => '+57 1 456 7890',
                'category_id' => 1,
            ],
            [
                'name' => 'Ricoh Colombia',
                'email' => 'contacto@ricoh.com.co',
                'phone' => '+57 1 567 8901',
                'category_id' => 1,
            ],
            [
                'name' => 'Distribuidora Local',
                'email' => 'ventas@distlocal.com',
                'phone' => '+57 300 123 4567',
                'category_id' => 2,
            ],
        ];

        foreach ($suppliers as $supplier) {
            ProductSupplier::firstOrCreate(
                ['email' => $supplier['email']], 
                $supplier
            );
        }

        $this->command->info('✅ Proveedores de productos creados para TES LTDA');
    }
}
