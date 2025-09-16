<?php

namespace App\Filament\Resources\UserResource\Widgets;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Models\ProductCategory;
use App\Models\ProductSupplier;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class UserOverview extends BaseWidget
{
    protected function getStats(): array
    {
        //current year
        $year = Carbon::now()->year;
        //variable to store each order count as array.
        $new_orders_count = [];
        //Looping through the month array to get count for each month in the provided year
        for ($i = 1; $i <= 12; $i++) {
            $new_orders_count[] = Order::whereYear('updated_at', $year)
                ->whereMonth('updated_at', $i)
                ->count();
        }

        return [
            Stat::make(Str::plural('Usuario', User::all()->count()), User::all()->count())
                ->description('Total de usuarios')
                ->icon('heroicon-m-users')
                ->color('primary'),
            Stat::make(Str::plural('Categoría de Producto', ProductCategory::all()->count()), ProductCategory::all()->count())
                ->description('Total de categorías')
                ->icon('heroicon-m-bookmark')
                ->color('primary'),
            Stat::make(Str::plural('Proveedor de Producto', ProductSupplier::all()->count()), ProductSupplier::all()->count())
                ->description('Total de proveedores')
                ->icon('heroicon-m-document-plus')
                ->color('primary'),
            Stat::make(Str::plural('Producto', Product::all()->count()), Product::all()->count())
                ->description('Total de productos')
                ->icon('heroicon-m-queue-list')
                ->color('primary'),
            Stat::make(Str::plural('Orden', Order::all()->count()), Order::all()->count())
                ->description('Total de órdenes')
                ->icon('heroicon-m-document-check')
                ->color('primary')
                ->chart($new_orders_count)
                ->chartColor('success'),
            Stat::make('Stock Bajo', Product::where('quantity', '<=', 10)
                ->whereHas('category', function($query) {
                    $query->whereIn('slug', [
                        'toners-originales',
                        'toners-genericos', 
                        'toners-remanufacturados',
                        'tintas'
                    ]);
                })
                ->count())
                ->description('Tintas y tóners con stock bajo')
                ->icon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->chartColor('danger'),
        ];
    }
}
