<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use App\Models\ProductSupplier;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProductResource;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Generar slug único si el nombre ha cambiado
        if (!empty($data['name'])) {
            $recordId = $this->record->id;
            $currentSlug = $this->record->slug;
            $newSlug = \App\Models\Product::generateUniqueSlug($data['name'], $recordId);
            
            // Solo actualizar si el slug generado es diferente al actual
            if ($newSlug !== $currentSlug) {
                $data['slug'] = $newSlug;
            }
        }

        return $data;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title("Product updated")
            ->body("The product has been updated successfully.")
            ->icon('heroicon-o-rectangle-group')
            ->color('success');
    }
}
