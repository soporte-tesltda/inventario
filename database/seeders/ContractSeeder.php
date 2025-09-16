<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Contract;
use App\Models\Product;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener algunos productos
        $impresora1 = Product::where('name', 'HP LaserJet Pro M404n')->first();
        $impresora2 = Product::where('name', 'Canon PIXMA G3110')->first();
        $computadora = Product::where('name', 'Dell OptiPlex 3090')->first();        // Contratos de ejemplo
        if ($impresora1) {
            Contract::firstOrCreate([
                'client_name' => 'Empresa ABC S.A.',
                'product_id' => $impresora1->id,
                'contract_type' => 'renta',
            ], [
                'product_id' => $impresora1->id,
                'contract_type' => 'renta',
                'client_name' => 'Empresa ABC S.A.',
                'client_company' => 'Empresa ABC S.A.',
                'client_phone' => '+52-555-1234',
                'client_email' => 'juan.perez@empresaabc.com',
                'start_date' => now()->subMonths(3),
                'end_date' => now()->addMonths(9),
                'rental_price' => 850.00,
                'status' => 'activo',
                'notes' => 'Renta mensual de impresora HP LaserJet Pro M404n. Incluye mantenimiento preventivo mensual. Cliente prioritario. Renovación automática.'
            ]);
        }

        if ($impresora2) {
            Contract::firstOrCreate([
                'client_name' => 'Cliente XYZ Ltda.',
                'product_id' => $impresora2->id,
                'contract_type' => 'mantenimiento',
            ], [
                'product_id' => $impresora2->id,
                'contract_type' => 'mantenimiento',
                'client_name' => 'María González',
                'client_company' => 'Cliente XYZ Ltda.',
                'client_phone' => '+52-555-5678',
                'client_email' => 'maria.gonzalez@clientexyz.com',
                'start_date' => now()->subMonths(1),
                'end_date' => now()->addMonths(11),
                'rental_price' => 450.00,
                'status' => 'activo',
                'notes' => 'Contrato de mantenimiento preventivo y correctivo para impresora Canon PIXMA G3110. Incluye reemplazo de piezas menores.'
            ]);
        }

        if ($computadora) {
            Contract::firstOrCreate([
                'client_name' => 'Director General',
                'product_id' => $computadora->id,
                'contract_type' => 'garantia',
            ], [
                'product_id' => $computadora->id,
                'contract_type' => 'garantia',
                'client_name' => 'Director General',
                'client_company' => 'Oficina Central',
                'client_phone' => '+52-555-9999',
                'client_email' => 'director@oficina.com',
                'start_date' => now()->subDays(30),
                'end_date' => now()->addYears(2)->subDays(30),
                'rental_price' => 0.00,
                'status' => 'activo',
                'notes' => 'Garantía extendida de 2 años para computadora Dell OptiPlex 3090. Garantía del fabricante. Cobertura total.'            ]);
        }
    }
}
