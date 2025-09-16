<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Models\User;
use App\Models\Product;
use App\Mail\LowStockAlert;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected ?string $heading = 'Crear Orden';

    protected ?string $subheading = 'Crear una nueva orden de venta';

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title("Orden creada")
            ->body("La orden ha sido creada exitosamente.")
            ->icon('heroicon-o-document-text')
            ->color('success');
    }

    protected function beforeCreate(): void
    {
        foreach ($this->data['orderProducts'] as $order) {
            $product = Product::find($order['product_id']);

            if (!$product) {
                Notification::make()
                    ->error()
                    ->title("Producto no encontrado")
                    ->body('El producto con ID ' . $order['product_id'] . ' no existe.')
                    ->persistent()
                    ->send();

                $this->halt();
            }

            if ($product->quantity < $order['quantity']) {
                Notification::make()
                    ->warning()
                    ->title("Stock insuficiente")
                    ->body('La cantidad requerida para el producto ' . $product->name . ' no está disponible. Cantidad disponible: ' . $product->quantity)
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }

        foreach ($this->data['orderProducts'] as $order) {
            $product = Product::find($order['product_id']);

            if ($product) {
                $product->decrement('quantity', $order['quantity']);
            }
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Solo considerar productos de tintas y tóners para alertas de stock bajo
        $lowStockProducts = Product::where('quantity', '<=', 10)
            ->whereHas('category', function($query) {
                $query->whereIn('slug', [
                    'toners-originales',
                    'toners-genericos', 
                    'toners-remanufacturados',
                    'tintas'
                ]);
            })
            ->get(['name', 'quantity']);

        if ($lowStockProducts->isNotEmpty()) {
            $adminUser = User::find(1, ['name', 'email']);

            $emailData = [
                // 'subject' => 'Low Stocks Alert',
                'products' => $lowStockProducts,
                'user' => $adminUser,
            ];

            Mail::send(new LowStockAlert($emailData));
        }
    }
}
