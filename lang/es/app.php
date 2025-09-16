<?php

return [
    // Navegación
    'navigation' => [
        'stocks_management' => 'Gestión de Inventario',
        'users_management' => 'Gestión de Usuarios',
        'products' => 'Productos',
        'categories' => 'Categorías',
        'suppliers' => 'Proveedores',
        'orders' => 'Órdenes',
        'contracts' => 'Contratos',
        'users' => 'Usuarios',
        'roles' => 'Roles',
    ],

    // Productos
    'products' => [
        'title' => 'Productos',
        'singular' => 'Producto',
        'plural' => 'Productos',
        'create' => 'Crear Producto',
        'edit' => 'Editar Producto',
        'delete' => 'Eliminar Producto',
        'view' => 'Ver Producto',
        'fields' => [
            'name' => 'Nombre',
            'slug' => 'Slug',
            'serial' => 'Número de Serie',
            'location' => 'Ubicación',
            'price' => 'Precio',
            'quantity' => 'Cantidad',
            'category' => 'Categoría',
            'supplier' => 'Proveedor',
            'rental_status' => 'Estado de Renta',
            'company_client' => 'Cliente Empresa',
            'area' => 'Área',
            'detailed_location' => 'Ubicación Detallada',
            'brand_model' => 'Marca/Modelo',
            'expiration_date' => 'Fecha de Vencimiento',
            'image' => 'Imagen',
        ],
        'rental_status' => [
            'para_la_renta' => 'Para la Renta',
            'renta' => 'En Renta',
            'no_disponible' => 'No Disponible',
        ],
    ],

    // Categorías
    'categories' => [
        'title' => 'Categorías de Productos',
        'singular' => 'Categoría',
        'plural' => 'Categorías',
        'create' => 'Crear Categoría',
        'edit' => 'Editar Categoría',
        'delete' => 'Eliminar Categoría',
        'fields' => [
            'title' => 'Título',
            'slug' => 'Slug',
            'product_type' => 'Tipo de Producto',
        ],
        'product_types' => [
            'hardware' => 'Hardware',
            'consumable' => 'Consumible',
            'accessory' => 'Accesorio',
            'service' => 'Servicio',
        ],
    ],

    // Proveedores
    'suppliers' => [
        'title' => 'Proveedores',
        'singular' => 'Proveedor',
        'plural' => 'Proveedores',
        'create' => 'Crear Proveedor',
        'edit' => 'Editar Proveedor',
        'delete' => 'Eliminar Proveedor',
        'fields' => [
            'name' => 'Nombre',
            'email' => 'Correo Electrónico',
            'phone' => 'Teléfono',
            'address' => 'Dirección',
        ],
    ],

    // Órdenes
    'orders' => [
        'title' => 'Órdenes',
        'singular' => 'Orden',
        'plural' => 'Órdenes',
        'create' => 'Crear Orden',
        'edit' => 'Editar Orden',
        'delete' => 'Eliminar Orden',
        'fields' => [
            'order_number' => 'Número de Orden',
            'order_date' => 'Fecha de Orden',
            'client_name' => 'Nombre del Cliente',
            'total' => 'Total',
            'status' => 'Estado',
        ],
    ],

    // Usuarios
    'users' => [
        'title' => 'Usuarios',
        'singular' => 'Usuario',
        'plural' => 'Usuarios',
        'create' => 'Crear Usuario',
        'edit' => 'Editar Usuario',
        'delete' => 'Eliminar Usuario',
        'fields' => [
            'name' => 'Nombre',
            'email' => 'Correo Electrónico',
            'password' => 'Contraseña',
            'roles' => 'Roles',
        ],
    ],

    // Contratos
    'contracts' => [
        'title' => 'Contratos',
        'singular' => 'Contrato',
        'plural' => 'Contratos',
        'create' => 'Crear Contrato',
        'edit' => 'Editar Contrato',
        'delete' => 'Eliminar Contrato',
    ],

    // Mensajes generales
    'messages' => [
        'success' => '¡Operación exitosa!',
        'error' => 'Ha ocurrido un error',
        'created' => 'Registro creado exitosamente',
        'updated' => 'Registro actualizado exitosamente',
        'deleted' => 'Registro eliminado exitosamente',
        'confirm_delete' => '¿Está seguro de que desea eliminar este registro?',
        'no_records' => 'No hay registros disponibles',
        'loading' => 'Cargando...',
    ],

    // Botones y acciones
    'actions' => [
        'create' => 'Crear',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'view' => 'Ver',
        'save' => 'Guardar',
        'cancel' => 'Cancelar',
        'close' => 'Cerrar',
        'search' => 'Buscar',
        'filter' => 'Filtrar',
        'export' => 'Exportar',
        'import' => 'Importar',
        'refresh' => 'Actualizar',
    ],

    // Información de la empresa
    'company' => [
        'name' => 'TES LTDA',
        'description' => 'Empresa especializada en impresoras y tóners',
        'address' => 'Bogotá, Colombia',
        'phone' => '+57 1 234 5678',
        'email' => 'info@tesltda.com',
    ],
];
