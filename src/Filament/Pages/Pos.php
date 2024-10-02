<?php

namespace TomatoPHP\FilamentPos\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use TomatoPHP\FilamentCms\Models\Category;
use TomatoPHP\FilamentEcommerce\Filament\Resources\OrderResource;
use TomatoPHP\FilamentEcommerce\Models\Cart;
use TomatoPHP\FilamentEcommerce\Models\Order;
use TomatoPHP\FilamentEcommerce\Models\Product;
use TomatoPHP\FilamentPos\Filament\Pages\Traits\HasCart;
use TomatoPHP\FilamentPos\Filament\Pages\Traits\HasCheckout;
use TomatoPHP\FilamentPos\Filament\Pages\Traits\HasShield;
use TomatoPHP\FilamentPos\Filament\Pages\Traits\HasProducts;
use TomatoPHP\FilamentPos\Filament\Pages\Traits\HasSearch;

class Pos extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithFormActions;
    use InteractsWithTable;
    use HasCart;
    use HasCheckout;
    use HasShield;


    public static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static string $view = 'filament-pos::pages.pos';

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

    public function mount()
    {
        if(!session()->has('sessionID')){
            session()->put('sessionID', Str::uuid());
        }
        else {
            $this->sessionID = session()->get('sessionID');
        }
    }

    public function table(Table $table): Table
    {
        return $table->query(\TomatoPHP\FilamentEcommerce\Models\Product::query()->where('is_activated', 1))
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('feature_image')
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
                    ->state(fn(Product $product) => ($product->price+$product->vat) - $product->discount)
                    ->description(fn(Product $product) => '(Price:'.number_format($product->price, 2) . '+VAT:' . number_format($product->vat) . ')-Discount:' . number_format($product->discount))
                    ->money(locale: 'en', currency: setting('site_currency'))
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('addToCart')
                    ->label(trans('filament-pos::messages.table.actions.addToCart'))
                    ->tooltip(trans('filament-pos::messages.table.actions.addToCart'))
                    ->iconButton()
                    ->icon('heroicon-s-shopping-cart')
                    ->action(function($record){
                        $existsOnCart = Cart::query()
                            ->where('session_id', $this->sessionID)
                            ->where('product_id', $record->id)
                            ->first();
                        if(!$existsOnCart){
                            Cart::query()->create([
                                'session_id' => $this->sessionID,
                                'item' => $record->name,
                                'product_id' => $record->id,
                                'price' => $record->price,
                                'discount' => $record->discount,
                                'vat' => $record->vat,
                                'total' => ($record->price + $record->vat) - $record->discount,
                                'qty' => 1,
                            ]);
                        }
                        else {
                            $existsOnCart->qty += 1;
                            $existsOnCart->save();
                        }

                    }),
            ])
            ->searchPlaceholder(trans('filament-pos::messages.table.search'))
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(trans('filament-pos::messages.table.filters.category_id'))
                    ->searchable()
                    ->options(Category::query()
                        ->where('for', 'product')
                        ->where('type', 'category')
                        ->pluck('name', 'id')
                        ->toArray()
                    ),
            ])
            ->defaultSort('name', 'desc');
    }

    public function notify(string $title, string $type = 'success')
    {
        Notification::make()
            ->title(Str::title($type))
            ->body($title)
            ->status($type)
            ->send();
    }

    public function notifyAndPrint(Order $order)
    {
        Notification::make()
            ->title(trans('filament-pos::messages.notifications.checkout.title'))
            ->body(trans('filament-pos::messages.notifications.checkout.message', ['uuid' => $order->uuid]))
            ->success()
            ->actions([
                \Filament\Notifications\Actions\Action::make('print')
                    ->label(trans('filament-pos::messages.notifications.checkout.print'))
                    ->icon('heroicon-o-printer')
                    ->url(route('order.print', $order->id))
                    ->openUrlInNewTab(),
                \Filament\Notifications\Actions\Action::make('preview')
                    ->label(trans('filament-pos::messages.notifications.checkout.view'))
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->url(OrderResource::getUrl('view', ['record' => $order->id]))
                    ->openUrlInNewTab(),
            ])
            ->send();
    }

}
