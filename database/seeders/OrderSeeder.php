<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generar órdenes de muestra para TES LTDA
        Order::factory(20)->create();
        
        $this->command->info('✅ Órdenes de muestra creadas para TES LTDA');
    }
}
