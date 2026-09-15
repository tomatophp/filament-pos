<?php

namespace TomatoPHP\FilamentPos\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Illuminate\Database\Eloquent\Builder;
use TomatoPHP\FilamentEcommerce\Models\Order;
use TomatoPHP\FilamentPos\Filament\Widgets\Traits\HasShield;

class POSStateWidget extends BaseWidget
{
    use HasShield;

    protected function getColumns(): int
    {
        return 2;
    }

    protected function posOrders(): Builder
    {
        return Order::query()->where('source', 'pos');
    }

    protected function getStats(): array
    {
        $trend = Trend::query($this->posOrders())
            ->interval('day')
            ->dateColumn('created_at')
            ->between(
                now()->subMonth(),
                now()
            )
            ->count();

        $todayOrders = $this->posOrders()->whereDate('created_at', today());

        return [
            Stat::make(trans('filament-pos::messages.widgets.count'), (clone $todayOrders)->count())
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($trend->pluck('aggregate')->toArray())
                ->color('primary')
                ->icon('heroicon-s-shopping-cart'),
            Stat::make(trans('filament-pos::messages.widgets.money'), number_format((float) (clone $todayOrders)->sum('total'), 2))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($trend->pluck('aggregate')->toArray())
                ->color('info')
                ->icon('heroicon-s-currency-dollar'),
        ];
    }
}
