<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use Carbon\Carbon;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Gráfico de Órdenes';
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $year = Carbon::now()->year;

        $new_orders_count = collect(range(1, 12))
            ->map(function ($month) use ($year) {
                return Order::whereYear('updated_at', $year)
                    ->whereMonth('updated_at', $month)
                    ->count();
            })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Órdenes Completadas',
                    'data' => $new_orders_count,
                    'fill' => true,
                ],
            ],
            'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
