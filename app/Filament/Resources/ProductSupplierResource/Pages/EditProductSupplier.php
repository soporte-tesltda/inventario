<?php

namespace App\Filament\Resources\ProductSupplierResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProductSupplierResource;

class EditProductSupplier extends EditRecord
{
    protected static string $resource = ProductSupplierResource::class;

    protected ?string $heading = 'Editar Proveedor';

    protected ?string $subheading = 'Actualiza la información del proveedor';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }


    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title("Proveedor actualizado")
            ->body("El proveedor ha sido actualizado exitosamente.")
            ->icon('heroicon-o-queue-list')
            ->color('success');
    }
}
