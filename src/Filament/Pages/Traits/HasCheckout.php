<?php

namespace TomatoPHP\FilamentPos\Filament\Pages\Traits;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use TomatoPHP\FilamentAccounts\Models\Account;
use TomatoPHP\FilamentEcommerce\Facades\FilamentEcommerce;
use TomatoPHP\FilamentEcommerce\Models\Cart;
use TomatoPHP\FilamentEcommerce\Models\Coupon;
use TomatoPHP\FilamentEcommerce\Models\Order;
use TomatoPHP\FilamentEcommerce\Models\Product;
use TomatoPHP\FilamentTypes\Models\Type;

trait HasCheckout
{
    public function checkoutAction():Action
    {
        return Action::make('checkoutAction')
            ->label(trans('filament-pos::messages.actions.checkout.label'))
            ->requiresConfirmation()
            ->form(function(array $arguments){
                return [
                    Select::make('account_id')
                        ->label(trans('filament-pos::messages.actions.checkout.form.account_id'))
                        ->searchable([
                            'name',
                            'phone',
                            'email',
                            'username'
                        ])
                        ->createOptionForm([
                            Hidden::make('type')
                                ->default('account'),
                            Hidden::make('loginBy')
                                ->default('phone'),
                            Hidden::make('username')
                                ->unique('accounts', 'username'),
                            TextInput::make('name')
                                ->label(trans('filament-pos::messages.actions.checkout.account.name'))
                                ->required(),
                            TextInput::make('phone')
                                ->label(trans('filament-pos::messages.actions.checkout.account.phone'))
                                ->unique('accounts', 'phone')
                                ->afterStateUpdated(function (Get $get, Set $set){
                                    $set('username', $get('phone'));
                                })
                                ->required()
                                ->tel(),
                            TextInput::make('email')
                                ->label(trans('filament-pos::messages.actions.checkout.account.email'))
                                ->afterStateUpdated(function (Get $get, Set $set){
                                    $set('username', $get('email'));
                                })
                                ->email(),
                            Textarea::make('address')
                                ->label(trans('filament-pos::messages.actions.checkout.account.address')),
                        ])
                        ->createOptionUsing(function (array $data){
                            $data['is_active'] = 1;
                            $account = \TomatoPHP\FilamentAccounts\Models\Account::create($data);
                            return $account->id;
                        })
                        ->options(\TomatoPHP\FilamentAccounts\Models\Account::query()->where('is_active', 1)->pluck('name', 'id')),
                    Select::make('payment_method')
                        ->label(trans('filament-pos::messages.actions.checkout.form.payment_method'))
                        ->default('cash')
                        ->searchable()
                        ->options([
                            'cash' => 'Cash',
                            'credit_card' => 'Credit Card',
                            'wallet' => 'Wallet',
                            'gift-card' => 'Gift Card',
                        ])
                        ->required(),
                    TextInput::make('paid_amount')
                        ->label(trans('filament-pos::messages.actions.checkout.form.paid_amount'))
                        ->default($arguments['total'])
                        ->numeric()
                        ->required(),
                    Hidden::make('coupon_id'),
                    TextInput::make('coupon')
                        ->label(trans('filament-pos::messages.actions.checkout.form.coupon'))
                        ->suffixAction(
                            \Filament\Forms\Components\Actions\Action::make('apply')
                                ->tooltip('Apply')
                                ->icon('heroicon-s-check')
                                ->action(function (Get $get,Set $set){
                                    $coupon = Coupon::query()->where('code', $get('coupon'))->first();
                                    if($coupon){
                                        $items = $get('items');
                                        $total = 0;
                                        $vat = 0;
                                        $productIds = [];
                                        $discount = 0;
                                        foreach ($items as $orderItem){
                                            $productIds[] = $orderItem['product_id'];
                                            $product = Product::find($orderItem['product_id']);
                                            if($product){
                                                $getDiscount= 0;
                                                if($product->discount_to && Carbon::parse($product->discount_to)->isFuture()){
                                                    $getDiscount = $product->discount;
                                                }

                                                $discount+=$getDiscount;
                                                $vat+=$product->vat;
                                                $total += ((($product->price+$product->vat)-$getDiscount)*$orderItem['qty']);
                                            }
                                        }

                                        $getCouponDiscount = FilamentEcommerce::coupon()
                                            ->products($productIds)
                                            ->discount(code: $get('coupon'), total: $total);

                                        if($getCouponDiscount){
                                            $discount+=$getCouponDiscount;

                                            $set('discount', $discount);
                                            $set('total', ($total+$vat)-$discount);
                                            $set('coupon_id', $coupon->id);

                                            Notification::make()
                                                ->title(trans('filament-ecommerce::messages.orders.actions.coupon.success'))
                                                ->success()
                                                ->send();
                                        }
                                        else {
                                            Notification::make()
                                                ->title(trans('filament-ecommerce::messages.orders.actions.coupon.not_valid'))
                                                ->danger()
                                                ->send();
                                        }
                                    }
                                    else {
                                        Notification::make()
                                            ->title(trans('filament-ecommerce::messages.orders.actions.coupon.not_found'))
                                            ->danger()
                                            ->send();
                                    }

                                })
                        ),

                ];
            })
            ->action(function (array $data){
                $account = null;
                if(!$data['account_id']){
                    $account = Account::query()->where('username', 'pos')->first();
                    if(!$account){
                        $account = Account::create([
                            'name' => 'POS',
                            'username' => 'pos',
                            'is_active' => 1,
                        ]);
                    }
                }
                else {
                    $account = Account::find($data['account_id']);
                }

                $cart = Cart::query()->where('session_id', $this->sessionID)->get();

                $order = Order::query()->create([
                    'user_id' => auth()->user()->id,
                    'account_id' => $account->id,
                    'source' => 'pos',
                    'uuid' => setting('ordering_stating_code') .'-POS-'. \Illuminate\Support\Str::random(8),
                    'branch_id' => setting('ordering_direct_branch'),
                    'company_id' => setting('ordering_company_id'),
                    'payment_method' => $data['payment_method'],
                    'total' => $cart->sum(function ($item){
                        return $item->total * $item->qty;
                    }),
                    'discount' => $cart->sum(function ($item){
                        return $item->discount * $item->qty;
                    }),
                    'vat' => $cart->sum(function ($item){
                        return $item->vat * $item->qty;
                    }),
                    'coupon_id' => $data['coupon_id']??null,
                    'status' => 'paid',
                    'is_approved' => 1,
                    'is_closed' => 1,
                    'is_payed' => 1,
                ]);

                $order->ordersItems()->createMany($cart->map(function ($item) use ($account){
                    $item['account_id'] = $account->id;
                    return $item;
                })->toArray());


                Cart::query()->where('session_id', $this->sessionID)->delete();


                $this->notifyAndPrint($order);
            });
    }
}
