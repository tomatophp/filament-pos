<?php

namespace TomatoPHP\FilamentPos\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use TomatoPHP\FilamentCms\Models\Category;
use TomatoPHP\FilamentEcommerce\Filament\Resources\OrderResource;
use TomatoPHP\FilamentEcommerce\Models\Order;
use TomatoPHP\FilamentEcommerce\Models\Product;
use TomatoPHP\FilamentPos\Filament\Pages\Traits\HasCart;
use TomatoPHP\FilamentPos\Filament\Pages\Traits\HasCheckout;
use TomatoPHP\FilamentPos\Filament\Pages\Traits\HasShield;

class Pos extends Page implements HasForms, HasTable
{
    use HasCart;
    use HasCheckout;
    use HasShield;
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected string $view = 'filament-pos::pages.pos';

    public ?string $sessionID = null;

    public static function getNavigationGroup(): ?string
    {
        return trans('filament-pos::messages.group');
    }

    public static function getNavigationLabel(): string
    {
        return trans('filament-pos::messages.title');
    }

    public function getTitle(): string|Htmlable
    {
        return trans('filament-pos::messages.title');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('orders')
                ->label('POS Orders')
                ->icon('heroicon-o-clipboard')
                ->url(OrderResource::getUrl('index')),
        ];
    }

    public function mount(): void
    {
        if (! session()->has('sessionID')) {
            session()->put('sessionID', (string) Str::uuid());
        }

        $this->sessionID = (string) session()->get('sessionID');
    }

    public function table(Table $table): Table
    {
        return $table->query(Product::query()->where('is_activated', 1))
            ->columns([
                SpatieMediaLibraryImageColumn::make('feature_image')
                    ->label(trans('filament-pos::messages.table.columns.image'))
                    ->square()
                    ->collection('feature_image'),
                TextColumn::make('name')
                    ->label(trans('filament-pos::messages.table.columns.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label(trans('filament-pos::messages.table.columns.sku'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('barcode')
                    ->label(trans('filament-pos::messages.table.columns.barcode'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label(trans('filament-pos::messages.table.columns.price'))
                    ->state(fn (Product $product) => ($product->price + $product->vat) - $product->discount)
                    ->description(fn (Product $product) => '(Price:'.number_format($product->price, 2).'+VAT:'.number_format($product->vat).')-Discount:'.number_format($product->discount))
                    ->money(locale: 'en', currency: setting('site_currency') ?: null)
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('addToCart')
                    ->label(trans('filament-pos::messages.table.actions.addToCart'))
                    ->tooltip(trans('filament-pos::messages.table.actions.addToCart'))
                    ->iconButton()
                    ->icon('heroicon-s-shopping-cart')
                    ->action(fn (Product $record) => $this->addToCart($record)),
            ])
            ->searchPlaceholder(trans('filament-pos::messages.table.search'))
            ->filters([
                SelectFilter::make('category_id')
                    ->label(trans('filament-pos::messages.table.filters.category_id'))
                    ->searchable()
                    ->options(fn () => Category::query()
                        ->where('for', 'product')
                        ->where('type', 'category')
                        ->pluck('name', 'id')
                        ->toArray()),
            ])
            ->defaultSort('name', 'desc');
    }

    public function notify(string $title, string $type = 'success'): void
    {
        Notification::make()
            ->title(Str::title($type))
            ->body($title)
            ->status($type)
            ->send();
    }

    public function notifyAndPrint(Order $order): void
    {
        Notification::make()
            ->title(trans('filament-pos::messages.notifications.checkout.title'))
            ->body(trans('filament-pos::messages.notifications.checkout.message', ['uuid' => $order->uuid]))
            ->success()
            ->actions([
                Action::make('print')
                    ->label(trans('filament-pos::messages.notifications.checkout.print'))
                    ->icon('heroicon-o-printer')
                    ->url(route('order.print', $order->id))
                    ->openUrlInNewTab(),
                Action::make('preview')
                    ->label(trans('filament-pos::messages.notifications.checkout.view'))
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->url(OrderResource::getUrl('view', ['record' => $order->id]))
                    ->openUrlInNewTab(),
            ])
            ->send();
    }
}
