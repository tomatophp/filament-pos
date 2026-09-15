<?php

namespace TomatoPHP\FilamentPos\Filament\Pages\Traits;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use TomatoPHP\FilamentAccounts\Models\Account;
use TomatoPHP\FilamentEcommerce\Facades\FilamentEcommerce;
use TomatoPHP\FilamentEcommerce\Models\Coupon;
use TomatoPHP\FilamentEcommerce\Models\Order;

trait HasCheckout
{
    public function checkoutAction(): Action
    {
        return Action::make('checkoutAction')
            ->label(trans('filament-pos::messages.actions.checkout.label'))
            ->requiresConfirmation()
            ->schema(function (array $arguments) {
                return [
                    Select::make('account_id')
                        ->label(trans('filament-pos::messages.actions.checkout.form.account_id'))
                        ->searchable([
                            'name',
                            'phone',
                            'email',
                            'username',
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
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('username', $get('phone'));
                                })
                                ->required()
                                ->tel(),
                            TextInput::make('email')
                                ->label(trans('filament-pos::messages.actions.checkout.account.email'))
                                ->email(),
                            Textarea::make('address')
                                ->label(trans('filament-pos::messages.actions.checkout.account.address')),
                        ])
                        ->createOptionUsing(function (array $data) {
                            $data['is_active'] = 1;
                            $data['username'] = $data['username'] ?? $data['phone'];

                            return Account::query()->create($data)->id;
                        })
                        ->options(fn () => Account::query()->where('is_active', 1)->pluck('name', 'id')),
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
                        ->default($arguments['total'] ?? 0)
                        ->numeric()
                        ->required(),
                    Hidden::make('coupon_id'),
                    TextInput::make('coupon')
                        ->label(trans('filament-pos::messages.actions.checkout.form.coupon'))
                        ->suffixAction(
                            Action::make('apply')
                                ->tooltip('Apply')
                                ->icon('heroicon-s-check')
                                ->action(function (Get $get, Set $set) {
                                    $coupon = Coupon::query()->where('code', $get('coupon'))->first();

                                    if (! $coupon) {
                                        Notification::make()
                                            ->title(trans('filament-ecommerce::messages.orders.actions.coupon.not_found'))
                                            ->danger()
                                            ->send();

                                        return;
                                    }

                                    $cart = $this->getCartItems();
                                    $couponDiscount = $this->getCouponDiscount($coupon, $cart);

                                    if (! $couponDiscount) {
                                        Notification::make()
                                            ->title(trans('filament-ecommerce::messages.orders.actions.coupon.not_valid'))
                                            ->danger()
                                            ->send();

                                        return;
                                    }

                                    $set('coupon_id', $coupon->id);
                                    $set('paid_amount', max(0, $this->getCartTotal($cart) - $couponDiscount));

                                    Notification::make()
                                        ->title(trans('filament-ecommerce::messages.orders.actions.coupon.success'))
                                        ->success()
                                        ->send();
                                })
                        ),
                ];
            })
            ->action(fn (array $data) => $this->checkout($data));
    }

    /**
     * @param  array{account_id?: int|string|null, payment_method: string, coupon_id?: int|string|null}  $data
     */
    public function checkout(array $data): ?Order
    {
        $cart = $this->getCartItems();

        if ($cart->isEmpty()) {
            return null;
        }

        $account = filled($data['account_id'] ?? null)
            ? Account::query()->find($data['account_id'])
            : null;

        $account ??= Account::query()->firstOrCreate(
            ['username' => 'pos'],
            ['name' => 'POS', 'is_active' => 1],
        );

        $coupon = filled($data['coupon_id'] ?? null) ? Coupon::query()->find($data['coupon_id']) : null;
        $couponDiscount = $coupon ? $this->getCouponDiscount($coupon, $cart) : 0;

        $order = Order::query()->create([
            'user_id' => auth()->id(),
            'account_id' => $account->id,
            'name' => $account->name,
            'phone' => $account->phone,
            'address' => $account->address,
            'source' => 'pos',
            'uuid' => setting('ordering_stating_code').'-POS-'.Str::random(8),
            'branch_id' => setting('ordering_direct_branch') ?: null,
            'company_id' => setting('ordering_company_id') ?: null,
            'payment_method' => $data['payment_method'],
            'total' => max(0, $this->getCartTotal($cart) - $couponDiscount),
            'discount' => $cart->sum(fn ($item) => $item->discount * $item->qty) + $couponDiscount,
            'vat' => $cart->sum(fn ($item) => $item->vat * $item->qty),
            'coupon_id' => $coupon?->id,
            'status' => 'paid',
            'is_approved' => 1,
            'is_closed' => 1,
            'is_payed' => 1,
        ]);

        $order->ordersItems()->createMany($cart->map(fn ($item) => [
            'account_id' => $account->id,
            'product_id' => $item->product_id,
            'item' => $item->item,
            'price' => $item->price,
            'discount' => $item->discount,
            'vat' => $item->vat,
            'total' => $item->total,
            'qty' => $item->qty,
            'options' => $item->options,
        ])->all());

        $this->cartQuery()->delete();

        $this->notifyAndPrint($order);

        return $order;
    }

    protected function getCartTotal(Collection $cart): float
    {
        return (float) $cart->sum(fn ($item) => $item->total * $item->qty);
    }

    protected function getCouponDiscount(Coupon $coupon, Collection $cart): float
    {
        return (float) FilamentEcommerce::coupon()
            ->products($cart->pluck('product_id')->filter()->values()->all())
            ->discount(code: $coupon->code, total: $this->getCartTotal($cart));
    }
}
