<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductCategory;

class MigrateComputerData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-computer-data {--dry-run : Solo mostrar qué se haría sin ejecutar cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar datos de computadores desde la estructura antigua a la nueva';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 MODO SIMULACIÓN - No se realizarán cambios');
        } else {
            $this->info('🚀 MIGRACIÓN DE DATOS DE COMPUTADORES');
        }
        
        // Buscar categorías
        $oldCategory = ProductCategory::where('slug', 'computadores-escritorio')->first();
        $newCategory = ProductCategory::where('slug', 'computadores')->first();
        
        if (!$oldCategory) {
            $this->error('❌ No se encontró la categoría "computadores-escritorio"');
            return 1;
        }
        
        if (!$newCategory) {
            $this->error('❌ No se encontró la categoría "computadores"');
            return 1;
        }
        
        $this->info("📁 Categoría antigua: {$oldCategory->title} (ID: {$oldCategory->id})");
        $this->info("📁 Categoría nueva: {$newCategory->title} (ID: {$newCategory->id})");
        
        // Obtener productos a migrar
        $products = Product::where('product_categories_id', $oldCategory->id)->get();
        
        if ($products->isEmpty()) {
            $this->info('ℹ️  No hay productos para migrar');
            return 0;
        }
        
        $this->info("📦 Productos encontrados: {$products->count()}");
        $this->newLine();
        
        $migratedCount = 0;
        
        foreach ($products as $product) {
            $this->info("🔄 Procesando: {$product->name} (Serial: {$product->serial})");
            
            $newData = [
                'product_categories_id' => $newCategory->id,
                'computer_type' => 'escritorio', // Asumir escritorio ya que venían de esa categoría
                'computer_brand' => $this->extractBrand($product->name),
                'computer_model' => $this->extractModel($product->name),
                'computer_serial' => $product->serial,
                'computer_status' => $this->mapStatus($product->rental_status),
                'computer_location' => $product->location,
                'assigned_user' => $product->company_client,
                'mouse_info' => $this->combineAccessoryInfo('Mouse', $product->mouse_brand_model, $product->mouse_serial),
                'keyboard_info' => $this->combineAccessoryInfo('Teclado', $product->keyboard_brand_model, $product->keyboard_serial),
                'monitor_info' => $this->combineAccessoryInfo('Monitor', $product->monitor_brand_model, $product->monitor_serial),
                'computer_specifications' => $this->combineSpecs($product),
                'computer_observations' => $product->detailed_location,
            ];
            
            if (!$isDryRun) {
                $product->update($newData);
            }
            
            $this->line("   ✅ Actualizado con:");
            $this->line("      - Tipo: {$newData['computer_type']}");
            $this->line("      - Marca: {$newData['computer_brand']}");
            $this->line("      - Modelo: {$newData['computer_model']}");
            $this->line("      - Estado: {$newData['computer_status']}");
            
            $migratedCount++;
        }
        
        $this->newLine();
        if ($isDryRun) {
            $this->info("🔍 SIMULACIÓN COMPLETADA: {$migratedCount} productos serían migrados");
        } else {
            $this->info("✅ MIGRACIÓN COMPLETADA: {$migratedCount} productos migrados exitosamente");
        }
        
        return 0;
    }
    
    /**
     * Extraer marca del nombre del producto
     */
    private function extractBrand(string $name): string
    {
        $brands = ['HP', 'Dell', 'Lenovo', 'ASUS', 'Acer', 'MSI', 'Apple', 'Compaq', 'Toshiba', 'Sony'];
        
        foreach ($brands as $brand) {
            if (stripos($name, $brand) !== false) {
                return $brand;
            }
        }
        
        // Si no encuentra marca conocida, tomar la primera palabra
        $words = explode(' ', $name);
        return $words[0] ?? 'No especificado';
    }
    
    /**
     * Extraer modelo del nombre del producto
     */
    private function extractModel(string $name): string
    {
        // Remover la marca y devolver el resto como modelo
        $brand = $this->extractBrand($name);
        $model = str_ireplace($brand, '', $name);
        return trim($model) ?: 'No especificado';
    }
    
    /**
     * Mapear estado anterior al nuevo
     */
    private function mapStatus(?string $oldStatus): string
    {
        return match($oldStatus) {
            'renta', 'para_la_renta' => 'asignado',
            'para_la_venta', 'vendidas' => 'disponible',
            'en_garantia' => 'en_garantia',
            'buen_estado' => 'operativo',
            'mal_estado', 'con_defecto' => 'en_reparacion',
            default => 'disponible'
        };
    }
    
    /**
     * Combinar información de accesorios
     */
    private function combineAccessoryInfo(string $type, ?string $brandModel, ?string $serial): ?string
    {
        $parts = array_filter([$brandModel, $serial ? "Serial: {$serial}" : null]);
        return !empty($parts) ? implode(' - ', $parts) : null;
    }
    
    /**
     * Combinar especificaciones técnicas
     */
    private function combineSpecs(Product $product): ?string
    {
        $specs = [];
        
        if ($product->processor) $specs[] = "Procesador: {$product->processor}";
        if ($product->ram_memory) $specs[] = "RAM: {$product->ram_memory}";
        if ($product->storage_type && $product->storage_capacity) {
            $storageType = match($product->storage_type) {
                'HDD' => 'Disco Duro (HDD)',
                'SSD' => 'Disco Sólido (SSD)',
                'HDD_SSD' => 'HDD + SSD',
                'M2_SSD' => 'M.2 SSD',
                default => $product->storage_type
            };
            $specs[] = "Almacenamiento: {$storageType} {$product->storage_capacity}";
        }
        if ($product->operating_system) {
            $os = match($product->operating_system) {
                'windows_10' => 'Windows 10',
                'windows_11' => 'Windows 11',
                'ubuntu' => 'Ubuntu Linux',
                'linux_mint' => 'Linux Mint',
                'sin_so' => 'Sin Sistema Operativo',
                default => $product->operating_system
            };
            $specs[] = "SO: {$os}";
        }
        if ($product->additional_components) $specs[] = "Adicional: {$product->additional_components}";
        
        return !empty($specs) ? implode(PHP_EOL, $specs) : null;
    }
}
