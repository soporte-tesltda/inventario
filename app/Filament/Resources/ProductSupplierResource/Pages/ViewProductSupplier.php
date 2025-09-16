<?php

namespace App\Filament\Resources\ProductSupplierResource\Pages;

use App\Filament\Resources\ProductSupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductSupplier extends ViewRecord
{
    protected static string $resource = ProductSupplierResource::class;

    protected ?string $heading = 'Ver Proveedor';

    protected ?string $subheading = 'Detalles del proveedor';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
