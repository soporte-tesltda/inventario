<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Campos específicos para computadores
            $table->enum('computer_type', [
                'escritorio', 'portatil', 'aio', 'mini_pc', 'workstation', 
                'gaming', 'servidor', 'tablet', 'otro'
            ])->nullable()->after('image'); // Tipo de computador (obligatorio)
            
            $table->string('computer_brand')->nullable()->after('computer_type'); // Marca (obligatorio)
            $table->string('computer_model')->nullable()->after('computer_brand'); // Modelo (obligatorio)
            $table->string('computer_serial')->nullable()->after('computer_model'); // Número de serie (obligatorio)
            $table->enum('computer_status', [
                'operativo', 'en_reparacion', 'fuera_servicio', 'en_mantenimiento',
                'asignado', 'disponible', 'dado_baja', 'en_garantia'
            ])->nullable()->after('computer_serial'); // Estado (obligatorio)
            
            $table->string('computer_location')->nullable()->after('computer_status'); // Ubicación
            $table->string('assigned_user')->nullable()->after('computer_location'); // Usuario asignado
            
            // Accesorios y periféricos
            $table->string('mouse_info')->nullable()->after('assigned_user'); // Mouse
            $table->string('keyboard_info')->nullable()->after('mouse_info'); // Teclado
            $table->string('charger_info')->nullable()->after('keyboard_info'); // Cargador
            $table->string('monitor_info')->nullable()->after('charger_info'); // Monitor
            $table->text('accessories')->nullable()->after('monitor_info'); // Accesorio(s)
            
            $table->text('computer_specifications')->nullable()->after('accessories'); // Especificaciones
            $table->text('computer_observations')->nullable()->after('computer_specifications'); // Observaciones
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'computer_type', 'computer_brand', 'computer_model', 'computer_serial',
                'computer_status', 'computer_location', 'assigned_user', 'mouse_info',
                'keyboard_info', 'charger_info', 'monitor_info', 'accessories',
                'computer_specifications', 'computer_observations'
            ]);
        });
    }
};
