<?php

namespace App\Filament\Resources\ProductCategoryResource\Pages;

use App\Filament\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductCategory extends CreateRecord
{
    protected static string $resource = ProductCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generar slug único si no se ha proporcionado o está vacío
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = ProductCategory::generateUniqueSlug($data['title']);
        }

        return $data;
    }
}
