<?php

namespace App\Filament\Resources\ProductSupplierResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ProductSupplierResource;

class CreateProductSupplier extends CreateRecord
{
    protected static string $resource = ProductSupplierResource::class;

    protected ?string $heading = 'Crear Proveedor';

    protected ?string $subheading = 'Crear un nuevo proveedor de productos';

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title("Proveedor creado")
            ->body("El proveedor ha sido creado exitosamente.")
            ->icon('heroicon-o-queue-list')
            ->color('success');
    }
}
