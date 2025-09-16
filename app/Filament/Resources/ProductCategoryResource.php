<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductCategoryResource\Pages;
use App\Models\ProductCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductCategoryResource extends Resource
{
    protected static ?string $model = ProductCategory::class;

    protected static ?string $slug = 'categorias';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Gestión de Inventario';

    protected static ?string $navigationLabel = 'Categorías';

    protected static ?string $modelLabel = 'Categoría';

    protected static ?string $pluralModelLabel = 'Categorías';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Nombre de la Categoría')
                    ->required()
                    ->maxLength(255)
                    ->live(debounce: 1000)
                    ->afterStateUpdated(function (Set $set, ?string $state, callable $get) {
                        if ($state) {
                            $recordId = $get('../../record.id') ?? null;
                            $uniqueSlug = ProductCategory::generateUniqueSlug($state, $recordId);
                            $set('slug', $uniqueSlug);
                        }
                    }),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique('product_categories', ignoreRecord: true)
                    ->helperText('Se genera automáticamente. Puedes editarlo si es necesario.')
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->validationMessages([
                        'regex' => 'El slug solo puede contener letras minúsculas, números y guiones.',
                    ])
                    ->suffixAction(
                        \Filament\Forms\Components\Actions\Action::make('regenerate-slug')
                            ->icon('heroicon-m-arrow-path')
                            ->action(function (Set $set, callable $get) {
                                $title = $get('title');
                                if ($title) {
                                    $recordId = $get('../../record.id') ?? null;
                                    $uniqueSlug = ProductCategory::generateUniqueSlug($title, $recordId);
                                    $set('slug', $uniqueSlug);
                                }
                            })
                            ->tooltip('Regenerar slug automáticamente')
                    ),
                Select::make('product_type')
                    ->label('Tipo de Producto')
                    ->options([
                        'hardware' => 'Hardware (Impresoras, Equipos)',
                        'consumable' => 'Consumibles (Tóneres, Papel)',
                        'accessory' => 'Accesorios (Cables, Cartuchos)',
                        'service' => 'Servicios (Mantenimiento, Instalación)',
                    ])
                    ->default('hardware')
                    ->required()
                    ->helperText('Selecciona el tipo para determinar qué campos serán requeridos en los productos de esta categoría'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hardware' => 'primary',
                        'consumable' => 'success',
                        'accessory' => 'warning',
                        'service' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'hardware' => 'Hardware',
                        'consumable' => 'Consumibles',
                        'accessory' => 'Accesorios',
                        'service' => 'Servicios',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_type')
                    ->label('Tipo de Producto')
                    ->options([
                        'hardware' => 'Hardware',
                        'consumable' => 'Consumibles',
                        'accessory' => 'Accesorios',
                        'service' => 'Servicios',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductCategories::route('/'),
            'create' => Pages\CreateProductCategory::route('/create'),
            'edit' => Pages\EditProductCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
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
}
