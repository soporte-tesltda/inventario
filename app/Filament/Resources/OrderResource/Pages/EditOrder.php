<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Models\User;
use Filament\Actions;
use App\Models\Product;
use App\Mail\LowStockAlert;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\OrderResource;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected ?string $heading = 'Editar Orden';

    protected ?string $subheading = 'Actualiza la información de la orden';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        if ($this->data['delivered']) {
            Notification::make()
                ->warning()
                ->title("La orden no puede ser editada")
                ->body('La orden ya ha sido entregada y no se puede editar.')
                ->persistent()
                ->send();

            $this->halt();
        }

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

            $originalOrder = $this->record->orderProducts->firstWhere('product_id', $order['product_id']);
            $originalQuantity = $originalOrder ? $originalOrder->quantity : 0;

            $availableQuantity = $product->quantity + $originalQuantity;

            if ($availableQuantity < $order['quantity']) {
                Notification::make()
                    ->warning()
                    ->title("Stock insuficiente")
                    ->body('La cantidad requerida para el producto ' . $product->name . ' no está disponible. Cantidad disponible: ' . $availableQuantity)
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }

        foreach ($this->data['orderProducts'] as $order) {
            $product = Product::find($order['product_id']);

            if ($product) {
                $originalOrder = $this->record->orderProducts->firstWhere('product_id', $order['product_id']);
                $originalQuantity = $originalOrder ? $originalOrder->quantity : 0;

                if ($order['quantity'] > $originalQuantity) {
                    // Decrease stock if the new quantity is greater than the original
                    $product->decrement('quantity', $order['quantity'] - $originalQuantity);
                } elseif ($order['quantity'] < $originalQuantity) {
                    // Increase stock if the new quantity is less than the original
                    $product->increment('quantity', $originalQuantity - $order['quantity']);
                }
            }
        }
    }

    protected function afterSave(): void
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
