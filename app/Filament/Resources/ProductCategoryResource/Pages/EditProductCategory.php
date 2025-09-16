<?php

namespace App\Filament\Resources\ProductCategoryResource\Pages;

use App\Filament\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductCategory extends EditRecord
{
    protected static string $resource = ProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Generar slug único si el título ha cambiado
        if (!empty($data['title'])) {
            $recordId = $this->record->id;
            $currentSlug = $this->record->slug;
            $newSlug = ProductCategory::generateUniqueSlug($data['title'], $recordId);
            
            // Solo actualizar si el slug generado es diferente al actual
            if ($newSlug !== $currentSlug) {
                $data['slug'] = $newSlug;
            }
        }

        return $data;
    }
}
