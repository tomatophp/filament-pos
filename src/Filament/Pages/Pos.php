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
use Illuminate\Support\Str;
use TomatoPHP\FilamentCms\Models\Category;
use TomatoPHP\FilamentEcommerce\Filament\Resources\OrderResource;
use TomatoPHP\FilamentEcommerce\Models\Cart;
use TomatoPHP\FilamentEcommerce\Models\Order;
use TomatoPHP\FilamentEcommerce\Models\Product;
use TomatoPHP\FilamentPos\Filament\Pages\Traits\HasCart;
use TomatoPHP\FilamentPos\Filament\Pages\Traits\HasCheckout;
use TomatoPHP\FilamentPos\Filament\Pages\Traits\HasProducts;
use TomatoPHP\FilamentPos\Filament\Pages\Traits\HasSearch;

class Pos extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithFormActions;
    use InteractsWithTable;
    use HasCart;
    use HasCheckout;


    public ?string $sessionID = null;


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
                    ->square()
                    ->collection('feature_image')
                    ->label('Image'),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('sku')->searchable()->sortable(),
                TextColumn::make('barcode')->searchable()->sortable(),
                TextColumn::make('price')
                    ->state(fn(Product $product) => ($product->price+$product->vat) - $product->discount)
                    ->description(fn(Product $product) => '(Price:'.number_format($product->price, 2) . '+VAT:' . number_format($product->vat) . ')-Discount:' . number_format($product->discount))
                    ->label(trans('filament-ecommerce::messages.product.columns.price'))
                    ->money(locale: 'en', currency: setting('site_currency'))
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('addToCart')
                    ->label('Add To Cart')
                    ->tooltip('Add To Cart')
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
            ->searchPlaceholder('Search By Product Name or Barcode Scan')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(trans('filament-ecommerce::messages.product.filters.category_id'))
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
            ->title('Order Placed')
            ->body('Order #'.$order->uuid.' Has Been Created', 'success')
            ->success()
            ->actions([
                \Filament\Notifications\Actions\Action::make('print')
                    ->label('Print Receipt')
                    ->icon('heroicon-o-printer')
                    ->url(route('order.print', $order->id))
                    ->openUrlInNewTab(),
                \Filament\Notifications\Actions\Action::make('preview')
                    ->label('Preview Receipt')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->url(OrderResource::getUrl('view', ['record' => $order->id]))
                    ->openUrlInNewTab(),
            ])
            ->send();
    }

    public static ?string $title = 'POS';

    protected static ?string $navigationLabel = 'POS';

    public static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static string $view = 'filament-pos::pages.pos';
}
