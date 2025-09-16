<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractsRelationManager extends RelationManager
{
    protected static string $relationship = 'contracts';

    protected static ?string $title = 'Contratos';

    public function form(Form $form): Form
    {
        return $form
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
                DatePicker::make('start_date')
                    ->label('Fecha de Inicio')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Fecha de Finalización'),
                TextInput::make('rental_price')
                    ->label('Precio de Renta')
                    ->numeric()
                    ->prefix('COP'),
                FileUpload::make('file_path')
                    ->label('Archivo del Contrato')
                    ->directory('contracts')
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->maxSize(10240)
                    ->downloadable()
                    ->openable()
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->label('Notas')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('client_name')
            ->columns([
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('client_company')
                    ->label('Empresa')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->date()
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
                    ->label('Tipo')
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nuevo Contrato'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record): string => $record->file_path ? asset('storage/' . $record->file_path) : '#')
                    ->openUrlInNewTab()
                    ->visible(fn ($record): bool => !empty($record->file_path)),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
