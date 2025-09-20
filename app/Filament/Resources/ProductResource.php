<?php

namespace App\Filament\Resources;

use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $slug = 'productos';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?int $navigationSort = 2;    protected static ?string $navigationGroup = 'Gestión de Inventario';

    protected static ?string $navigationLabel = 'Productos';

    protected static ?string $modelLabel = 'Producto';

    protected static ?string $pluralModelLabel = 'Productos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([                Section::make('Proveedor')
                    ->description('Proveedor y categoría del producto')
                    ->schema([                        Select::make('product_suppliers_id')
                            ->label('Nombre del Proveedor')
                            ->relationship(name: 'supplier', titleAttribute: 'name')
                            ->preload()
                            ->searchable()
                            ->native(false)
                            ->required(),                        Select::make('product_categories_id')
                            ->label('Categoría')
                            ->relationship(name: 'category', titleAttribute: 'title')
                            ->preload()
                            ->searchable()
                            ->native(false)
                            ->required()
                            ->live()                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state) {
                                    $category = \App\Models\ProductCategory::find($state);
                                    if ($category && $category->product_type !== 'hardware') {
                                        $set('serial', null);
                                        $set('company_client', null);
                                        $set('area', null);
                                        $set('detailed_location', null);
                                    }
                                }
                            }),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->hiddenOn('view'),                Section::make('Producto')
                    ->description('Detalles del producto')                    ->schema([                        FileUpload::make('image')
                            ->image()
                            ->columnSpanFull()
                            ->disk(env('FILAMENT_FILESYSTEM_DISK', 'private'))
                            ->directory('products')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120) // 5MB máximo
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('800')
                            ->imageResizeTargetHeight('800')
                            ->downloadable()
                            ->openable()
                            ->deletable()
                            ->reorderable(false)
                            ->helperText('Formatos soportados: JPG, PNG, WebP. Tamaño máximo: 5MB')
                            ->required(fn (string $context): bool => $context === 'create')
                            ->loadingIndicatorPosition('center')
                            ->removeUploadedFileButtonPosition('top-center')
                            ->uploadingMessage('Subiendo imagen...')
                            ->panelLayout('grid')
                            ->imagePreviewHeight('200')
                            ->extraAttributes([
                                'data-loading-text' => 'Cargando imagen...',
                            ]),TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(debounce: 1000)
                            ->afterStateUpdated(function (Set $set, ?string $state, callable $get) {
                                if ($state) {
                                    $recordId = $get('../../record.id') ?? null;
                                    $uniqueSlug = Product::generateUniqueSlug($state, $recordId);
                                    $set('slug', $uniqueSlug);
                                }
                            }),                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique('products', ignoreRecord: true)
                            ->helperText('Se genera automáticamente. Puedes editarlo si es necesario.')
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->validationMessages([
                                'regex' => 'El slug solo puede contener letras minúsculas, números y guiones.',
                            ])
                            ->suffixAction(
                                \Filament\Forms\Components\Actions\Action::make('regenerate-slug')
                                    ->icon('heroicon-m-arrow-path')
                                    ->action(function (Set $set, callable $get) {
                                        $name = $get('name');
                                        if ($name) {
                                            $recordId = $get('../../record.id') ?? null;
                                            $uniqueSlug = Product::generateUniqueSlug($name, $recordId);
                                            $set('slug', $uniqueSlug);
                                        }
                                    })
                                    ->tooltip('Regenerar slug automáticamente')
                            ),
                        TextInput::make('serial')
                            ->label('Serial')
                            ->maxLength(255)
                            ->visible(function (callable $get) {
                                $categoryId = $get('product_categories_id');
                                if (!$categoryId) return true;
                                $category = \App\Models\ProductCategory::find($categoryId);
                                return $category && $category->product_type === 'hardware';
                            })
                            ->required(function (callable $get) {
                                $categoryId = $get('product_categories_id');
                                if (!$categoryId) return false;
                                $category = \App\Models\ProductCategory::find($categoryId);
                                return $category && $category->product_type === 'hardware';
                            }),                        TextInput::make('location')
                            ->label('Ubicación')
                            ->maxLength(255)
                            ->visible(function (callable $get) {
                                $categoryId = $get('product_categories_id');
                                if (!$categoryId) return false;
                                $category = \App\Models\ProductCategory::find($categoryId);
                                return $category && $category->product_type === 'hardware';
                            })
                            ->required(function (callable $get) {
                                $categoryId = $get('product_categories_id');
                                if (!$categoryId) return false;
                                $category = \App\Models\ProductCategory::find($categoryId);
                                return $category && $category->product_type === 'hardware';
                            }),                        Select::make('rental_status')
                            ->label('Estado del Producto')
                            ->options([
                                'renta' => 'En Renta',
                                'para_la_renta' => 'Para la Renta',
                                'para_la_venta' => 'Para la Venta',
                                'vendidas' => 'Vendidas',
                                'en_garantia' => 'En Garantía',
                                'clientes_externos' => 'Clientes Externos',
                                'buen_estado' => 'En Buen Estado',
                                'mal_estado' => 'En Mal Estado',
                                'con_defecto' => 'Defectuoso',
                                'otro' => 'Otro',
                            ])
                            ->native(false)
                            ->visible(function (callable $get) {
                                $categoryId = $get('product_categories_id');
                                if (!$categoryId) return false;
                                $category = \App\Models\ProductCategory::find($categoryId);
                                return $category && $category->product_type === 'hardware';
                            }),
                        TextInput::make('company_client')
                            ->label('Empresa/Cliente')
                            ->maxLength(255)
                            ->visible(function (callable $get) {
                                $categoryId = $get('product_categories_id');
                                if (!$categoryId) return false;
                                $category = \App\Models\ProductCategory::find($categoryId);
                                return $category && $category->product_type === 'hardware';
                            }),
                        TextInput::make('area')
                            ->label('Área')
                            ->maxLength(255)
                            ->visible(function (callable $get) {
                                $categoryId = $get('product_categories_id');
                                if (!$categoryId) return false;
                                $category = \App\Models\ProductCategory::find($categoryId);
                                return $category && $category->product_type === 'hardware';
                            }),
                        TextInput::make('detailed_location')
                            ->label('Ubicación Detallada')
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->visible(function (callable $get) {
                                $categoryId = $get('product_categories_id');
                                if (!$categoryId) return false;
                                $category = \App\Models\ProductCategory::find($categoryId);
                                return $category && $category->product_type === 'hardware';
                            }),
                        TextInput::make('brand_model')
                            ->label('Marca/Modelo Compatible')
                            ->maxLength(255)
                            ->visible(function (callable $get) {
                                $categoryId = $get('product_categories_id');
                                if (!$categoryId) return false;
                                $category = \App\Models\ProductCategory::find($categoryId);
                                return $category && in_array($category->product_type, ['consumable', 'accessory']);
                            })
                            ->helperText('Para tóneres: especifica las impresoras compatibles'),
                        TextInput::make('expiration_date')
                            ->label('Fecha de Vencimiento')
                            ->type('date')
                            ->visible(function (callable $get) {
                                $categoryId = $get('product_categories_id');
                                if (!$categoryId) return false;
                                $category = \App\Models\ProductCategory::find($categoryId);
                                return $category && $category->product_type === 'consumable';                            }),
                    ])
                    ->columns(2)
                    ->collapsible(),                Section::make('Información del Computador')
                    ->description('Datos principales del computador')
                    ->schema([
                        Select::make('computer_type')
                            ->label('Tipo de Computador')
                            ->options([
                                'escritorio' => 'Computador de Escritorio',
                                'portatil' => 'Computador Portátil/Laptop',
                                'aio' => 'All-in-One (AIO)',
                                'mini_pc' => 'Mini PC',
                                'workstation' => 'Workstation',
                                'gaming' => 'PC Gaming',
                                'servidor' => 'Servidor',
                                'tablet' => 'Tablet',
                                'otro' => 'Otro',
                            ])
                            ->native(false)
                            ->required()
                            ->placeholder('Seleccionar tipo de computador'),
                        
                        TextInput::make('computer_brand')
                            ->label('Marca')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: HP, Dell, Lenovo, ASUS, etc.'),
                            
                        TextInput::make('computer_model')
                            ->label('Modelo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: EliteDesk 800 G6, OptiPlex 7090, etc.'),
                            
                        TextInput::make('computer_serial')
                            ->label('Número de Serie')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Número de serie del fabricante'),
                            
                        Select::make('computer_status')
                            ->label('Estado')
                            ->options([
                                'operativo' => 'Operativo',
                                'en_reparacion' => 'En Reparación',
                                'fuera_servicio' => 'Fuera de Servicio',
                                'en_mantenimiento' => 'En Mantenimiento',
                                'asignado' => 'Asignado',
                                'disponible' => 'Disponible',
                                'dado_baja' => 'Dado de Baja',
                                'en_garantia' => 'En Garantía',
                            ])
                            ->native(false)
                            ->required()
                            ->default('disponible'),
                            
                        TextInput::make('computer_location')
                            ->label('Ubicación')
                            ->maxLength(255)
                            ->placeholder('Oficina, planta, área específica, etc.'),
                            
                        TextInput::make('assigned_user')
                            ->label('Usuario Asignado')
                            ->maxLength(255)
                            ->placeholder('Nombre del usuario o empleado asignado'),
                    ])
                    ->columns(2)
                    ->visible(function (callable $get) {
                        $categoryId = $get('product_categories_id');
                        if (!$categoryId) return false;
                        $category = \App\Models\ProductCategory::find($categoryId);
                        return $category && $category->slug === 'computadores';
                    })
                    ->collapsible(),
                    
                Section::make('Accesorios y Periféricos')
                    ->description('Información sobre accesorios incluidos')
                    ->schema([
                        TextInput::make('mouse_info')
                            ->label('Mouse')
                            ->maxLength(255)
                            ->placeholder('Ej: Mouse óptico HP, inalámbrico Logitech, etc.'),
                            
                        TextInput::make('keyboard_info')
                            ->label('Teclado')
                            ->maxLength(255)
                            ->placeholder('Ej: Teclado HP USB, mecánico, inalámbrico, etc.'),
                            
                        TextInput::make('charger_info')
                            ->label('Cargador')
                            ->maxLength(255)
                            ->placeholder('Ej: Cargador original 90W, adaptador universal, etc.'),
                            
                        TextInput::make('monitor_info')
                            ->label('Monitor')
                            ->maxLength(255)
                            ->placeholder('Ej: Monitor HP 24", pantalla integrada, etc.'),
                            
                        Textarea::make('accessories')
                            ->label('Accesorio(s)')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Otros accesorios incluidos: cables, base, bocinas, webcam, etc.'),
                    ])
                    ->columns(2)
                    ->visible(function (callable $get) {
                        $categoryId = $get('product_categories_id');
                        if (!$categoryId) return false;
                        $category = \App\Models\ProductCategory::find($categoryId);
                        return $category && $category->slug === 'computadores';
                    })
                    ->collapsible(),
                    
                Section::make('Especificaciones y Observaciones')
                    ->description('Detalles técnicos y notas adicionales')
                    ->schema([
                        Textarea::make('computer_specifications')
                            ->label('Especificaciones')
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Especificaciones técnicas detalladas: procesador, RAM, almacenamiento, tarjeta gráfica, sistema operativo, etc.'),
                            
                        Textarea::make('computer_observations')
                            ->label('Observaciones')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Notas, observaciones, historial de reparaciones, condiciones especiales, etc.'),
                    ])
                    ->visible(function (callable $get) {
                        $categoryId = $get('product_categories_id');
                        if (!$categoryId) return false;
                        $category = \App\Models\ProductCategory::find($categoryId);
                        return $category && $category->slug === 'computadores';
                    })
                    ->collapsible(),Section::make('Precio')
                    ->description('Precio y cantidad')
                    ->schema([
                        TextInput::make('price')
                            ->label('Precio')
                            ->required()
                            ->numeric()
                            ->prefix('COP'),
                        TextInput::make('quantity')
                            ->label('Cantidad')
                            ->required()
                            ->numeric(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['category', 'supplier']))            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Imagen')
                    ->height(50)
                    ->width(50)
                    ->square()
                    ->defaultImageUrl(url('/images/placeholder-product.svg')),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),                Tables\Columns\TextColumn::make('price')
                    ->money('COP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('category.title')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('serial')
                    ->label('Serial')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->visible(fn ($record) => $record && $record->category && $record->category->product_type === 'hardware'),
                Tables\Columns\TextColumn::make('brand_model')
                    ->label('Marca/Modelo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->visible(fn ($record) => $record && $record->category && in_array($record->category->product_type, ['consumable', 'accessory'])),
                Tables\Columns\TextColumn::make('location')
                    ->label('Ubicación')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->visible(fn ($record) => $record && $record->category && $record->category->product_type === 'hardware'),                Tables\Columns\TextColumn::make('rental_status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'renta' => 'danger',
                        'para_la_renta' => 'warning',
                        'para_la_venta' => 'success',
                        'vendidas' => 'gray',
                        'en_garantia' => 'info',
                        'clientes_externos' => 'primary',
                        'buen_estado' => 'success',
                        'mal_estado' => 'danger',
                        'con_defecto' => 'danger',
                        'otro' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'renta' => 'En Renta',
                        'para_la_renta' => 'Para la Renta',
                        'para_la_venta' => 'Para la Venta',
                        'vendidas' => 'Vendidas',
                        'en_garantia' => 'En Garantía',
                        'clientes_externos' => 'Clientes Externos',
                        'buen_estado' => 'En Buen Estado',
                        'mal_estado' => 'En Mal Estado',
                        'con_defecto' => 'Defectuoso',
                        'otro' => 'Otro',
                        default => $state,
                    })
                    ->visible(fn ($record) => $record && $record->category && $record->category->product_type === 'hardware'),
                Tables\Columns\TextColumn::make('expiration_date')
                    ->label('Vencimiento')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(function ($record) {
                        if (!$record || !$record->expiration_date) return 'gray';
                        $today = now();
                        $expiration = \Carbon\Carbon::parse($record->expiration_date);
                        $daysUntilExpiration = $today->diffInDays($expiration, false);
                        
                        if ($daysUntilExpiration < 0) return 'danger';
                        if ($daysUntilExpiration <= 30) return 'warning';
                        return 'success';
                    })
                    ->visible(fn ($record) => $record && $record->category && $record->category->product_type === 'consumable'),
                Tables\Columns\TextColumn::make('company_client')
                    ->label('Empresa/Cliente')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn ($record) => $record && $record->category && $record->category->product_type === 'hardware'),                Tables\Columns\TextColumn::make('area')
                    ->label('Área')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn ($record) => $record && $record->category && $record->category->product_type === 'hardware'),                // Columnas específicas para computadores
                Tables\Columns\TextColumn::make('computer_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'escritorio' => 'primary',
                        'portatil' => 'success',
                        'aio' => 'info',
                        'mini_pc' => 'warning',
                        'workstation' => 'danger',
                        'gaming' => 'purple',
                        'servidor' => 'gray',
                        'tablet' => 'cyan',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'escritorio' => 'Escritorio',
                        'portatil' => 'Portátil',
                        'aio' => 'All-in-One',
                        'mini_pc' => 'Mini PC',
                        'workstation' => 'Workstation',
                        'gaming' => 'Gaming',
                        'servidor' => 'Servidor',
                        'tablet' => 'Tablet',
                        'otro' => 'Otro',
                        default => $state,
                    })
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->visible(fn ($record) => $record && $record->category && $record->category->slug === 'computadores'),
                    
                Tables\Columns\TextColumn::make('computer_brand')
                    ->label('Marca')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->visible(fn ($record) => $record && $record->category && $record->category->slug === 'computadores'),
                    
                Tables\Columns\TextColumn::make('computer_model')
                    ->label('Modelo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->visible(fn ($record) => $record && $record->category && $record->category->slug === 'computadores'),
                    
                Tables\Columns\TextColumn::make('computer_serial')
                    ->label('Serial')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn ($record) => $record && $record->category && $record->category->slug === 'computadores'),
                    
                Tables\Columns\TextColumn::make('computer_status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'operativo' => 'success',
                        'en_reparacion' => 'warning',
                        'fuera_servicio' => 'danger',
                        'en_mantenimiento' => 'info',
                        'asignado' => 'primary',
                        'disponible' => 'success',
                        'dado_baja' => 'gray',
                        'en_garantia' => 'purple',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'operativo' => 'Operativo',
                        'en_reparacion' => 'En Reparación',
                        'fuera_servicio' => 'Fuera de Servicio',
                        'en_mantenimiento' => 'En Mantenimiento',
                        'asignado' => 'Asignado',
                        'disponible' => 'Disponible',
                        'dado_baja' => 'Dado de Baja',
                        'en_garantia' => 'En Garantía',
                        default => $state,
                    })
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->visible(fn ($record) => $record && $record->category && $record->category->slug === 'computadores'),
                    
                Tables\Columns\TextColumn::make('computer_location')
                    ->label('Ubicación')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn ($record) => $record && $record->category && $record->category->slug === 'computadores'),
                      Tables\Columns\TextColumn::make('assigned_user')
                    ->label('Usuario Asignado')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->visible(fn ($record) => $record && $record->category && $record->category->slug === 'computadores'),
                Tables\Columns\TextColumn::make('slug')
                    ->toggleable(isToggledHiddenByDefault: true),Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Creado'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Actualizado'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Eliminado'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ContractsRelationManager::class,
        ];
    }    public static function getPages(): array
    {        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        // Para la búsqueda global, NO incluir registros eliminados
        return parent::getEloquentQuery();
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'price',
            'quantity',
            'serial',
            'location',
            'company_client',
            'area',
            // Campos legacy de computadores de escritorio
            'monitor_serial',
            'keyboard_serial',
            'mouse_serial',
            'processor',
            'ram_memory',
            'storage_capacity',
            // Nuevos campos de computadores
            'computer_brand',
            'computer_model', 
            'computer_serial',
            'computer_location',
            'assigned_user',
            'mouse_info',
            'keyboard_info',
            'charger_info',
            'monitor_info',
            'accessories',
            'computer_specifications',
            'computer_observations',
        ];
    }    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [
            'Categoría' => $record->category?->title,
            'Precio' => $record->price,
            'Cantidad' => $record->quantity,
        ];

        // Agregar detalles específicos para computadores
        if ($record->category && $record->category->slug === 'computadores') {
            if ($record->computer_type) {
                $details['Tipo'] = match($record->computer_type) {
                    'escritorio' => 'Computador de Escritorio',
                    'portatil' => 'Computador Portátil/Laptop',
                    'aio' => 'All-in-One (AIO)',
                    'mini_pc' => 'Mini PC',
                    'workstation' => 'Workstation',
                    'gaming' => 'PC Gaming',
                    'servidor' => 'Servidor',
                    'tablet' => 'Tablet',
                    'otro' => 'Otro',
                    default => $record->computer_type,
                };
            }
            if ($record->computer_brand) $details['Marca'] = $record->computer_brand;
            if ($record->computer_model) $details['Modelo'] = $record->computer_model;
            if ($record->computer_serial) $details['Serial'] = $record->computer_serial;
            if ($record->computer_status) {
                $details['Estado'] = match($record->computer_status) {
                    'operativo' => 'Operativo',
                    'en_reparacion' => 'En Reparación',
                    'fuera_servicio' => 'Fuera de Servicio',
                    'en_mantenimiento' => 'En Mantenimiento',
                    'disponible' => 'Disponible',
                    'asignado' => 'Asignado',
                    'en_garantia' => 'En Garantía',
                    'dado_baja' => 'Dado de Baja',
                    default => $record->computer_status,
                };
            }
            if ($record->computer_location) $details['Ubicación'] = $record->computer_location;
            if ($record->assigned_user) $details['Usuario Asignado'] = $record->assigned_user;
        } 
        // Agregar detalles para productos hardware legacy
        elseif ($record->category && $record->category->product_type === 'hardware') {
            if ($record->serial) $details['Serial'] = $record->serial;
            if ($record->location) $details['Ubicación'] = $record->location;
            if ($record->rental_status) {
                $details['Estado'] = match($record->rental_status) {
                    'renta' => 'En Renta',
                    'para_la_renta' => 'Para la Renta',
                    'para_la_venta' => 'Para la Venta',
                    'vendidas' => 'Vendidas',
                    'en_garantia' => 'En Garantía',
                    'clientes_externos' => 'Clientes Externos',
                    'buen_estado' => 'En Buen Estado',
                    'mal_estado' => 'En Mal Estado',
                    'con_defecto' => 'Defectuoso',
                    'otro' => 'Otro',
                    default => $record->rental_status,
                };
            }
            // Agregar detalles específicos para computadores de escritorio legacy
            if ($record->category->slug === 'computadores-escritorio') {
                if ($record->processor) $details['Procesador'] = $record->processor;
                if ($record->ram_memory) $details['RAM'] = $record->ram_memory;
                if ($record->storage_capacity) $details['Almacenamiento'] = $record->storage_capacity;
            }
        }

        return $details;
    }public static function getNavigationBadge(): ?string
    {
        // Solo mostrar badge para categorías de tintas y tóners
        $lowStockCount = static::getModel()::where('quantity', '<=', '10')
            ->withoutTrashed()
            ->whereHas('category', function($query) {
                $query->whereIn('slug', [
                    'toners-originales',
                    'toners-genericos', 
                    'toners-remanufacturados',
                    'tintas'
                ]);
            })
            ->count();
        return $lowStockCount > 0 ? (string) $lowStockCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Número de productos con stock bajo (tintas y tóners)';
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información del Producto')
                    ->schema([
                        Infolists\Components\ImageEntry::make('image_url')
                            ->label('Imagen')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('name')
                            ->label('Nombre'),
                        Infolists\Components\TextEntry::make('category.title')
                            ->label('Categoría'),
                        Infolists\Components\TextEntry::make('supplier.name')
                            ->label('Proveedor'),
                        Infolists\Components\TextEntry::make('price')
                            ->label('Precio')
                            ->money('COP'),
                        Infolists\Components\TextEntry::make('quantity')
                            ->label('Cantidad')
                            ->badge(),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Detalles Específicos')
                    ->schema([
                        Infolists\Components\TextEntry::make('serial')
                            ->label('Serial')
                            ->visible(fn ($record) => $record && $record->category && $record->category->product_type === 'hardware'),
                        Infolists\Components\TextEntry::make('location')
                            ->label('Ubicación')
                            ->visible(fn ($record) => $record && $record->category && $record->category->product_type === 'hardware'),                        Infolists\Components\TextEntry::make('rental_status')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'renta' => 'En Renta',
                                'para_la_renta' => 'Para la Renta',
                                'para_la_venta' => 'Para la Venta',
                                'vendidas' => 'Vendidas',
                                'en_garantia' => 'En Garantía',
                                'clientes_externos' => 'Clientes Externos',
                                'buen_estado' => 'En Buen Estado',
                                'mal_estado' => 'En Mal Estado',
                                'con_defecto' => 'Defectuoso',
                                'otro' => 'Otro',
                                default => $state,
                            })
                            ->visible(fn ($record) => $record && $record->category && $record->category->product_type === 'hardware'),
                        Infolists\Components\TextEntry::make('company_client')
                            ->label('Empresa/Cliente')
                            ->visible(fn ($record) => $record && $record->category && $record->category->product_type === 'hardware'),
                        Infolists\Components\TextEntry::make('area')
                            ->label('Área')
                            ->visible(fn ($record) => $record && $record->category && $record->category->product_type === 'hardware'),
                        Infolists\Components\TextEntry::make('detailed_location')
                            ->label('Ubicación Detallada')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record && $record->category && $record->category->product_type === 'hardware'),
                        Infolists\Components\TextEntry::make('brand_model')
                            ->label('Marca/Modelo Compatible')
                            ->visible(fn ($record) => $record && $record->category && in_array($record->category->product_type, ['consumable', 'accessory'])),
                        Infolists\Components\TextEntry::make('expiration_date')
                            ->label('Fecha de Vencimiento')
                            ->date()
                            ->badge()
                            ->color(function ($record) {
                                if (!$record || !$record->expiration_date) return 'gray';
                                $today = now();
                                $expiration = \Carbon\Carbon::parse($record->expiration_date);
                                $daysUntilExpiration = $today->diffInDays($expiration, false);
                                
                                if ($daysUntilExpiration < 0) return 'danger';
                                if ($daysUntilExpiration <= 30) return 'warning';
                                return 'success';
                            })                            ->visible(fn ($record) => $record && $record->category && $record->category->product_type === 'consumable'),
                    ])
                    ->columns(2),                Infolists\Components\Section::make('Información del Computador')
                    ->schema([
                        Infolists\Components\TextEntry::make('computer_type')
                            ->label('Tipo de Computador')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'escritorio' => 'Computador de Escritorio',
                                'portatil' => 'Computador Portátil/Laptop',
                                'aio' => 'All-in-One (AIO)',
                                'mini_pc' => 'Mini PC',
                                'workstation' => 'Workstation',
                                'gaming' => 'PC Gaming',
                                'servidor' => 'Servidor',
                                'tablet' => 'Tablet',
                                'otro' => 'Otro',
                                default => $state ?? 'No especificado',
                            })
                            ->color(fn (?string $state): string => match ($state) {
                                'escritorio' => 'blue',
                                'portatil' => 'green',
                                'aio' => 'purple',
                                'mini_pc' => 'orange',
                                'workstation' => 'red',
                                'gaming' => 'pink',
                                'servidor' => 'gray',
                                'tablet' => 'cyan',
                                'otro' => 'yellow',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('computer_brand')
                            ->label('Marca'),
                        Infolists\Components\TextEntry::make('computer_model')
                            ->label('Modelo'),
                        Infolists\Components\TextEntry::make('computer_serial')
                            ->label('Número de Serie')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('computer_status')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'operativo' => 'Operativo',
                                'en_reparacion' => 'En Reparación',
                                'fuera_servicio' => 'Fuera de Servicio',
                                'en_mantenimiento' => 'En Mantenimiento',
                                'disponible' => 'Disponible',
                                'asignado' => 'Asignado',
                                'en_garantia' => 'En Garantía',
                                'dado_baja' => 'Dado de Baja',
                                default => $state ?? 'No especificado',
                            })
                            ->color(fn (?string $state): string => match ($state) {
                                'operativo' => 'success',
                                'en_reparacion' => 'warning',
                                'fuera_servicio' => 'danger',
                                'en_mantenimiento' => 'info',
                                'disponible' => 'primary',
                                'asignado' => 'gray',
                                'en_garantia' => 'purple',
                                'dado_baja' => 'dark',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('computer_location')
                            ->label('Ubicación'),
                        Infolists\Components\TextEntry::make('assigned_user')
                            ->label('Usuario Asignado'),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record && $record->category && $record->category->slug === 'computadores'),

                Infolists\Components\Section::make('Accesorios y Periféricos')
                    ->schema([
                        Infolists\Components\TextEntry::make('mouse_info')
                            ->label('Mouse')
                            ->placeholder('No especificado'),
                        Infolists\Components\TextEntry::make('keyboard_info')
                            ->label('Teclado')
                            ->placeholder('No especificado'),
                        Infolists\Components\TextEntry::make('charger_info')
                            ->label('Cargador')
                            ->placeholder('No especificado'),
                        Infolists\Components\TextEntry::make('monitor_info')
                            ->label('Monitor')
                            ->placeholder('No especificado'),
                        Infolists\Components\TextEntry::make('accessories')
                            ->label('Accesorios Adicionales')
                            ->columnSpanFull()
                            ->placeholder('No especificado'),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record && $record->category && $record->category->slug === 'computadores'),

                Infolists\Components\Section::make('Especificaciones y Observaciones')
                    ->schema([
                        Infolists\Components\TextEntry::make('computer_specifications')
                            ->label('Especificaciones Técnicas')
                            ->columnSpanFull()
                            ->placeholder('No especificado'),
                        Infolists\Components\TextEntry::make('computer_observations')
                            ->label('Observaciones')
                            ->columnSpanFull()
                            ->placeholder('No hay observaciones'),
                    ])
                    ->columns(1)
                    ->visible(fn ($record) => $record && $record->category && $record->category->slug === 'computadores'),
                Infolists\Components\Section::make('Metadatos')
                    ->schema([
                        Infolists\Components\TextEntry::make('slug')
                            ->label('URL Slug'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Creado')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Actualizado')
                            ->dateTime(),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }
}
