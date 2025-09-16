<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSupplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniqueSlugTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear datos necesarios para las pruebas
        $this->category = ProductCategory::create([
            'title' => 'Test Category',
            'slug' => 'test-category',
            'product_type' => 'consumable'
        ]);
        
        $this->supplier = ProductSupplier::create([
            'name' => 'Test Supplier',
            'email' => 'test@supplier.com',
            'phone' => '1234567890',
            'category_id' => $this->category->id
        ]);
    }

    /** @test */
    public function it_generates_unique_slugs_for_products()
    {
        // Primer producto
        $slug1 = Product::generateUniqueSlug('Tóner HP LaserJet');
        $this->assertEquals('toner-hp-laserjet', $slug1);
        
        Product::create([
            'name' => 'Tóner HP LaserJet',
            'slug' => $slug1,
            'price' => 100000,
            'quantity' => 10,
            'product_categories_id' => $this->category->id,
            'product_suppliers_id' => $this->supplier->id,
            'image' => 'test.jpg'
        ]);
        
        // Segundo producto con el mismo nombre
        $slug2 = Product::generateUniqueSlug('Tóner HP LaserJet');
        $this->assertEquals('toner-hp-laserjet-1', $slug2);
        
        // Tercer producto con el mismo nombre
        Product::create([
            'name' => 'Tóner HP LaserJet',
            'slug' => $slug2,
            'price' => 100000,
            'quantity' => 10,
            'product_categories_id' => $this->category->id,
            'product_suppliers_id' => $this->supplier->id,
            'image' => 'test.jpg'
        ]);
        
        $slug3 = Product::generateUniqueSlug('Tóner HP LaserJet');
        $this->assertEquals('toner-hp-laserjet-2', $slug3);
    }

    /** @test */
    public function it_generates_unique_slugs_for_categories()
    {
        // Primera categoría
        $slug1 = ProductCategory::generateUniqueSlug('Tóners Originales');
        $this->assertEquals('toners-originales', $slug1);
        
        ProductCategory::create([
            'title' => 'Tóners Originales',
            'slug' => $slug1,
            'product_type' => 'consumable'
        ]);
        
        // Segunda categoría con el mismo nombre
        $slug2 = ProductCategory::generateUniqueSlug('Tóners Originales');
        $this->assertEquals('toners-originales-1', $slug2);
    }

    /** @test */
    public function it_handles_special_characters_in_slugs()
    {
        $slug = Product::generateUniqueSlug('Tóner HP 85A (CE285A) - Ñoño & Más!!!');
        $this->assertEquals('toner-hp-85a-ce285a-nono-mas', $slug);
    }

    /** @test */
    public function it_handles_empty_strings()
    {
        $slug = Product::generateUniqueSlug('');
        $this->assertEquals('producto', $slug);
        
        $categorySlug = ProductCategory::generateUniqueSlug('');
        $this->assertEquals('categoria', $categorySlug);
    }

    /** @test */
    public function it_ignores_current_record_when_editing()
    {
        $product = Product::create([
            'name' => 'Producto Test',
            'slug' => 'producto-test',
            'price' => 100000,
            'quantity' => 10,
            'product_categories_id' => $this->category->id,
            'product_suppliers_id' => $this->supplier->id,
            'image' => 'test.jpg'
        ]);
        
        // Al editar el mismo producto, debería mantener el mismo slug
        $newSlug = Product::generateUniqueSlug('Producto Test', $product->id);
        $this->assertEquals('producto-test', $newSlug);
    }
}
