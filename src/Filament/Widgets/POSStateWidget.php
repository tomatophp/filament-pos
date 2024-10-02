<?php

namespace TomatoPHP\FilamentPos\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Flowframe\Trend\Trend;
use Filament\Widgets\StatsOverviewWidget\Stat;
use TomatoPHP\FilamentEcommerce\Models\Order;
use TomatoPHP\FilamentPos\Filament\Widgets\Traits\HasShield;

class POSStateWidget extends BaseWidget
{
    use HasShield;

    protected static string $view = 'filament-widgets::stats-overview-widget';

    protected function getColumns(): int
    {
        return 2;
    }

    protected function getStats(): array
    {
        $orderQuery = Order::query();

        $trend = Trend::query((clone $orderQuery)->where('source', 'pos'))
            ->interval('day')
            ->dateColumn('created_at')
            ->between(
                now()->subMonth(),
                now()
            )
            ->count();

        return [
            Stat::make(trans('filament-pos::messages.widgets.count'), (clone $orderQuery)->where('source', 'pos')->whereDay('created_at',Carbon::today())->count())
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->value((clone $orderQuery)->where('source', 'pos')->whereDay('created_at',Carbon::today())->count())
                ->chart($trend->pluck('aggregate')->toArray())
                ->color('primary')
                ->icon('heroicon-s-shopping-cart'),
            Stat::make(trans('filament-pos::messages.widgets.money'), (clone $orderQuery)->where('source', 'pos')->whereDay('created_at',Carbon::today())->sum('total'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->value(number_format((clone $orderQuery)->where('source', 'pos')->whereDay('created_at',Carbon::today())->sum('total'), 2))
                ->chart($trend->pluck('aggregate')->toArray())
                ->color('info')
                ->icon('heroicon-s-currency-dollar'),
        ];
    }
}
