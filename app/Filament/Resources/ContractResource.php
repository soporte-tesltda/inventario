<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Contract;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ContractResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static ?string $slug = 'contratos';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Gestión de Inventario';

    protected static ?string $navigationLabel = 'Contratos';

    protected static ?string $modelLabel = 'Contrato';

    protected static ?string $pluralModelLabel = 'Contratos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Producto')
                    ->description('Seleccionar producto para el contrato')
                    ->schema([
                        Select::make('product_id')
                            ->label('Producto')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} - {$record->serial}"),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('Información del Contrato')
                    ->description('Detalles del contrato')
                    ->schema([
                        Select::make('contract_type')
                            ->label('Tipo de Contrato')
                            ->options([
                                'administrativo' => 'Administrativo',
                                'piezas' => 'Piezas',
                                'mantenimiento' => 'Mantenimiento',
                                'renta' => 'Renta',
                                'garantia' => 'Garantía',
                            ])
                            ->required(),
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'activo' => 'Activo',
                                'vencido' => 'Vencido',
                                'cancelado' => 'Cancelado',
                                'completado' => 'Completado',
                            ])
                            ->default('activo')
                            ->required(),
                        DatePicker::make('start_date')
                            ->label('Fecha de Inicio')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('Fecha de Finalización'),
                        TextInput::make('rental_price')
                            ->label('Precio de Renta')
                            ->numeric()
                            ->prefix('COP'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Información del Cliente')
                    ->description('Datos del cliente')
                    ->schema([
                        TextInput::make('client_name')
                            ->label('Nombre del Cliente')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('client_company')
                            ->label('Empresa')
                            ->maxLength(255),
                        TextInput::make('client_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('client_phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Documentos y Notas')
                    ->description('Archivos adjuntos y notas adicionales')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('Archivo del Contrato')
                            ->directory('contracts')
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240) // 10MB
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.serial')
                    ->label('Serial')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contract_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'administrativo' => 'info',
                        'piezas' => 'warning',
                        'mantenimiento' => 'success',
                        'renta' => 'danger',
                        'garantia' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'administrativo' => 'Administrativo',
                        'piezas' => 'Piezas',
                        'mantenimiento' => 'Mantenimiento',
                        'renta' => 'Renta',
                        'garantia' => 'Garantía',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_company')
                    ->label('Empresa')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Fecha Inicio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fecha Fin')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rental_price')
                    ->label('Precio')
                    ->money('COP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'activo' => 'success',
                        'vencido' => 'danger',
                        'cancelado' => 'warning',
                        'completado' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'activo' => 'Activo',
                        'vencido' => 'Vencido',
                        'cancelado' => 'Cancelado',
                        'completado' => 'Completado',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('file_path')
                    ->label('Archivo')
                    ->boolean()
                    ->trueIcon('heroicon-o-document')
                    ->falseIcon('heroicon-o-x-mark'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('contract_type')
                    ->label('Tipo de Contrato')
                    ->options([
                        'administrativo' => 'Administrativo',
                        'piezas' => 'Piezas',
                        'mantenimiento' => 'Mantenimiento',
                        'renta' => 'Renta',
                        'garantia' => 'Garantía',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'activo' => 'Activo',
                        'vencido' => 'Vencido',
                        'cancelado' => 'Cancelado',
                        'completado' => 'Completado',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Contract $record): string => $record->file_path ? asset('storage/' . $record->file_path) : '#')
                    ->openUrlInNewTab()
                    ->visible(fn (Contract $record): bool => !empty($record->file_path)),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'view' => Pages\ViewContract::route('/{record}'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $activeContracts = static::getModel()::where('status', 'activo')
            ->withoutTrashed()
            ->count();
        return $activeContracts > 0 ? (string) $activeContracts : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Número de contratos activos';
    }
}
