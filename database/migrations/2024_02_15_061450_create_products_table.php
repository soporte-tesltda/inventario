<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(Product::class);
            $table->string('serial')->nullable(); // Campo nullable para productos consumibles
            
            // Campos específicos para computadores de escritorio
            $table->string('monitor_serial')->nullable();
            $table->string('monitor_brand_model')->nullable();
            $table->enum('monitor_status', [
                'renta', 'para_la_renta', 'para_la_venta', 'vendidas', 
                'en_garantia', 'clientes_externos', 'buen_estado', 
                'mal_estado', 'con_defecto', 'otro'
            ])->nullable();
            
            $table->string('keyboard_serial')->nullable();
            $table->string('keyboard_brand_model')->nullable();
            $table->enum('keyboard_status', [
                'renta', 'para_la_renta', 'para_la_venta', 'vendidas', 
                'en_garantia', 'clientes_externos', 'buen_estado', 
                'mal_estado', 'con_defecto', 'otro'
            ])->nullable();
            
            $table->string('mouse_serial')->nullable();
            $table->string('mouse_brand_model')->nullable();
            $table->enum('mouse_status', [
                'renta', 'para_la_renta', 'para_la_venta', 'vendidas', 
                'en_garantia', 'clientes_externos', 'buen_estado', 
                'mal_estado', 'con_defecto', 'otro'
            ])->nullable();
            
            // Especificaciones técnicas del computador
            $table->string('processor')->nullable();
            $table->string('ram_memory')->nullable();
            $table->string('storage_type')->nullable();
            $table->string('storage_capacity')->nullable();
            $table->string('operating_system')->nullable();
            $table->text('additional_components')->nullable();
            
            $table->string('location')->nullable(); // Campo nullable sin valor por defecto
            $table->enum('rental_status', [
                'renta',
                'para_la_renta', 
                'para_la_venta', 
                'vendidas', 
                'en_garantia', 
                'clientes_externos',
                'buen_estado',
                'mal_estado', 
                'con_defecto',
                'otro'
            ])->nullable(); // Sin valor por defecto, nullable
            $table->string('company_client')->nullable();
            $table->string('area')->nullable();
            $table->text('detailed_location')->nullable();
            $table->string('brand_model')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 12, 2, true);
            $table->integer('quantity');
            $table->foreignId('product_categories_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('product_suppliers_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
