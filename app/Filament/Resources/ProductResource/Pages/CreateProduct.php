<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use App\Models\ProductSupplier;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ProductResource;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generar slug único si no se ha proporcionado o está vacío
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = \App\Models\Product::generateUniqueSlug($data['name']);
        }

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title("Producto creado")
            ->body("El producto ha sido creado exitosamente.")
            ->icon('heroicon-o-rectangle-group')
            ->color('success');
    }
}
