<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSupplier;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener categorías por slug (más confiable)
        $categorias = [
            'impresoras' => ProductCategory::where('slug', 'impresoras')->first(),
            'multifuncionales' => ProductCategory::where('slug', 'multifuncionales')->first(),
            'toners_originales' => ProductCategory::where('slug', 'toners-originales')->first(),
            'toners_genericos' => ProductCategory::where('slug', 'toners-genericos')->first(),
            'tintas' => ProductCategory::where('slug', 'tintas')->first(),
            'memorias' => ProductCategory::where('slug', 'memorias-usb')->first(),
            'computadores' => ProductCategory::where('slug', 'computadores')->first(),
        ];

        // Obtener proveedores
        $tesLtda = ProductSupplier::where('name', 'TES LTDA')->first();
        $hp = ProductSupplier::where('name', 'HP Colombia')->first();
        $canon = ProductSupplier::where('name', 'Canon Colombia')->first();

        // HARDWARE - IMPRESORAS
        if ($categorias['impresoras'] && $tesLtda) {
            $this->createProduct([
                'name' => 'Impresora Ricoh MP 501',
                'slug' => 'impresora-ricoh-mp-501',
                'serial' => 'G98766TT',
                'location' => 'Oficina TES LTDA',
                'price' => 3450000.00,
                'quantity' => 5,
                'product_categories_id' => $categorias['impresoras']->id,
                'product_suppliers_id' => $tesLtda->id,
                'rental_status' => 'para_la_renta',
                'company_client' => null,
                'area' => 'Inventario',
                'detailed_location' => 'Oficina TES LTDA - Área de almacén',
                'image' => 'impresoraricohmp501.jpg'
            ]);

            $this->createProduct([
                'name' => 'HP LaserJet Pro M404n',
                'slug' => 'hp-laserjet-pro-m404n',
                'serial' => 'HP001234567',
                'location' => 'Cliente ABC - Oficina 201',
                'price' => 4500000.00,
                'quantity' => 1,
                'product_categories_id' => $categorias['impresoras']->id,
                'product_suppliers_id' => $hp->id ?? $tesLtda->id,
                'rental_status' => 'renta',
                'company_client' => 'Empresa ABC',
                'area' => 'Sistemas',
                'detailed_location' => 'Oficina 201, Rack A'
            ]);
        }

        // CONSUMIBLES - TONERS ORIGINALES
        if ($categorias['toners_originales'] && $hp) {
            $this->createProduct([
                'name' => 'Toner HP 58A Original (CF258A)',
                'slug' => 'toner-hp-58a-original-cf258a',
                'serial' => 'TN001',
                'location' => 'Almacén TES LTDA',
                'price' => 1200000.00,
                'quantity' => 15,
                'product_categories_id' => $categorias['toners_originales']->id,
                'product_suppliers_id' => $hp->id ?? $tesLtda->id,
                'brand_model' => 'HP LaserJet Pro M404, M428, M429',
                'expiration_date' => now()->addMonths(24)
            ]);
        }

        // CONSUMIBLES - TONERS GENÉRICOS
        if ($categorias['toners_genericos'] && $tesLtda) {
            $this->createProduct([
                'name' => 'Toner HP 26A Compatible (CF226A)',
                'slug' => 'toner-hp-26a-compatible-cf226a',
                'serial' => 'TN002',
                'location' => 'Almacén TES LTDA',
                'price' => 450000.00,
                'quantity' => 8,
                'product_categories_id' => $categorias['toners_genericos']->id,
                'product_suppliers_id' => $tesLtda->id,
                'brand_model' => 'HP LaserJet Pro M402, M426',
                'expiration_date' => now()->addDays(15)
            ]);
        }

        // ACCESORIOS - MEMORIAS USB
        if ($categorias['memorias'] && $tesLtda) {
            $this->createProduct([
                'name' => 'Memoria USB Kingston 32GB',
                'slug' => 'memoria-usb-kingston-32gb',
                'serial' => 'MEM001',
                'location' => 'Almacén TES LTDA',
                'price' => 250000.00,
                'quantity' => 25,
                'product_categories_id' => $categorias['memorias']->id,
                'product_suppliers_id' => $tesLtda->id,
                'brand_model' => 'Compatible con todos los dispositivos USB'
            ]);
        }

        // HARDWARE - COMPUTADORES
        if ($categorias['computadores'] && $tesLtda) {
            $this->createProduct([
                'name' => 'Computador de Escritorio HP EliteDesk 800 G6',
                'slug' => 'computador-hp-elitedesk-800-g6',
                'price' => 2500000.00,
                'quantity' => 1,
                'product_categories_id' => $categorias['computadores']->id,
                'product_suppliers_id' => $hp->id ?? $tesLtda->id,
                'image' => 'computador-hp-elitedesk.jpg',
                // Nuevos campos específicos de computadores
                'computer_type' => 'escritorio',
                'computer_brand' => 'HP',
                'computer_model' => 'EliteDesk 800 G6',
                'computer_serial' => 'PC001-HP-2024',
                'computer_status' => 'disponible',
                'computer_location' => 'Oficina TES LTDA - Área de almacén, Estante A',
                'assigned_user' => null,
                'mouse_info' => 'HP Ratón Óptico USB - Serial: MS001-HP-OPT',
                'keyboard_info' => 'HP Teclado Estándar USB - Serial: KB001-HP-STD',
                'charger_info' => 'Fuente de poder original HP 180W',
                'monitor_info' => 'HP 24" Full HD IPS - Serial: MON001-HP-24',
                'accessories' => 'Cable HDMI, Cable de red Ethernet',
                'computer_specifications' => 'Procesador: Intel Core i5-10500' . PHP_EOL .
                                           'RAM: 16GB DDR4' . PHP_EOL .
                                           'Almacenamiento: 512GB SSD' . PHP_EOL .
                                           'SO: Windows 11' . PHP_EOL .
                                           'Adicional: Tarjeta de red Gigabit, Wi-Fi 6, Bluetooth 5.0, 6x USB 3.0, 2x USB 2.0',
                'computer_observations' => 'Equipo en excelente estado, listo para renta',
            ]);

            $this->createProduct([
                'name' => 'Computador de Escritorio Dell OptiPlex 7090',
                'slug' => 'computador-dell-optiplex-7090',
                'price' => 2800000.00,
                'quantity' => 1,
                'product_categories_id' => $categorias['computadores']->id,
                'product_suppliers_id' => $tesLtda->id,
                'image' => 'computador-dell-optiplex.jpg',
                // Nuevos campos específicos de computadores
                'computer_type' => 'escritorio',
                'computer_brand' => 'Dell',
                'computer_model' => 'OptiPlex 7090',
                'computer_serial' => 'PC002-DELL-2024',
                'computer_status' => 'asignado',
                'computer_location' => 'Cliente XYZ - Oficina Principal',
                'assigned_user' => 'Empresa XYZ - Área de Contabilidad',
                'mouse_info' => 'Dell Ratón Inalámbrico - Serial: MS002-DELL-WIRE',
                'keyboard_info' => 'Dell Teclado Mecánico - Serial: KB002-DELL-MECH',
                'charger_info' => 'Fuente de poder original Dell 200W',
                'monitor_info' => 'Dell 27" 4K UHD - Serial: MON002-DELL-27',
                'accessories' => 'Receptor inalámbrico para mouse y teclado, Cable DisplayPort',
                'computer_specifications' => 'Procesador: Intel Core i7-11700' . PHP_EOL .
                                           'RAM: 32GB DDR4' . PHP_EOL .
                                           'Almacenamiento: 256GB SSD + 1TB HDD' . PHP_EOL .
                                           'SO: Windows 10' . PHP_EOL .
                                           'Adicional: Tarjeta gráfica NVIDIA GT 1030, DVD-RW, Lector de tarjetas SD',
                'computer_observations' => 'Actualmente en renta en Cliente XYZ, ubicado en escritorio 3 de contabilidad',
            ]);

            $this->createProduct([
                'name' => 'Mini PC Lenovo ThinkCentre M75q',
                'slug' => 'mini-pc-lenovo-thinkcentre-m75q',
                'price' => 2200000.00,
                'quantity' => 1,
                'product_categories_id' => $categorias['computadores']->id,
                'product_suppliers_id' => $tesLtda->id,
                'image' => 'computador-lenovo-thinkcentre.jpg',
                // Nuevos campos específicos de computadores
                'computer_type' => 'mini_pc',
                'computer_brand' => 'Lenovo',
                'computer_model' => 'ThinkCentre M75q',
                'computer_serial' => 'PC003-LEN-2024',
                'computer_status' => 'en_garantia',
                'computer_location' => 'Oficina TES LTDA - Taller de reparaciones, Mesa 2',
                'assigned_user' => 'Soporte Técnico',
                'mouse_info' => 'Ratón Genérico Óptico - Serial: MS003-GEN-OPT',
                'keyboard_info' => 'Teclado Genérico USB - Serial: KB003-GEN-STD',
                'charger_info' => 'Adaptador original Lenovo 65W',
                'monitor_info' => 'Monitor Genérico 22" LED - Serial: MON003-GEN-22',
                'accessories' => 'Soporte VESA para monitor, Cable USB-C',
                'computer_specifications' => 'Procesador: AMD Ryzen 5 PRO 4650G' . PHP_EOL .
                                           'RAM: 8GB DDR4' . PHP_EOL .
                                           'Almacenamiento: 256GB M.2 NVMe SSD' . PHP_EOL .
                                           'SO: Ubuntu Linux' . PHP_EOL .
                                           'Adicional: Mini PC compacto, WiFi 6, Bluetooth 5.1, 4x USB 3.0',
                'computer_observations' => 'Equipo en garantía, usado para pruebas de software Linux',
            ]);
        }

        $this->command->info('✅ Productos de muestra creados para TES LTDA');
    }

    /**
     * Método auxiliar para crear productos evitando duplicados
     */
    private function createProduct(array $data): void
    {
        // Determinar el campo serial a usar basado en el tipo de producto
        $serialField = 'serial';
        $serialValue = $data['serial'] ?? $data['computer_serial'] ?? null;
        
        if (!$serialValue) {
            // Si no hay serial, usar el slug como identificador único
            $serialField = 'slug';
            $serialValue = $data['slug'];
        }
        
        Product::firstOrCreate(
            [$serialField => $serialValue], 
            $data
        );
    }
}
