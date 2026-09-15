<?php

use Filament\Actions\Testing\TestAction;
use TomatoPHP\FilamentAccounts\Models\Account;
use TomatoPHP\FilamentEcommerce\Database\Factories\AccountFactory;
use TomatoPHP\FilamentEcommerce\Database\Factories\BranchFactory;
use TomatoPHP\FilamentEcommerce\Database\Factories\CompanyFactory;
use TomatoPHP\FilamentEcommerce\Database\Factories\CouponFactory;
use TomatoPHP\FilamentEcommerce\Database\Factories\ProductFactory;
use TomatoPHP\FilamentEcommerce\Models\Cart;
use TomatoPHP\FilamentEcommerce\Models\Order;
use TomatoPHP\FilamentPos\Filament\Pages\Pos;
use TomatoPHP\FilamentPos\Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());
    seedOrderTypes();

    // The ordering settings default to company / branch #1.
    $this->company = CompanyFactory::new()->create();
    $this->branch = BranchFactory::new()->for($this->company)->create();
});

it('serves the pos page with products over http', function () {
    ProductFactory::new()->create(['name' => ['en' => 'Tomato Juice'], 'price' => 20, 'vat' => 2]);

    $this->get(Pos::getUrl())
        ->assertSuccessful()
        ->assertSee('Tomato Juice')
        ->assertSee('No items in cart');
});

it('lists the active products in the pos table', function () {
    $active = ProductFactory::new()->count(2)->create();
    $inactive = ProductFactory::new()->create(['is_activated' => false]);

    livewire(Pos::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($active)
        ->assertCanNotSeeTableRecords([$inactive]);
});

it('adds products to the cart of the current session on the first visit', function () {
    // The session id used to be stored without being assigned to the page on the first visit.
    $product = ProductFactory::new()->create(['price' => 100, 'vat' => 10, 'discount' => 0]);

    livewire(Pos::class)
        ->callTableAction('addToCart', $product)
        ->callTableAction('addToCart', $product)
        ->assertSee('Checkout');

    $item = Cart::query()->sole();

    expect($item->session_id)->not->toBeNull()
        ->and($item->session_id)->toBe(session('sessionID'))
        ->and($item->qty)->toEqual(2)
        ->and((float) $item->total)->toBe(110.0);
});

it('removes an item and clears the cart', function () {
    [$first, $second] = ProductFactory::new()->count(2)->create();

    $page = livewire(Pos::class)
        ->callTableAction('addToCart', $first)
        ->callTableAction('addToCart', $second);

    $page->call('removeFromCart', Cart::query()->where('product_id', $first->id)->value('id'));
    expect(Cart::query()->pluck('product_id')->all())->toBe([$second->id]);

    $page->call('clearCart');
    expect(Cart::query()->count())->toBe(0);
});

it('checks out the cart into a paid pos order with items and totals', function () {
    $juice = ProductFactory::new()->create(['price' => 100, 'vat' => 10, 'discount' => 0]);
    $bread = ProductFactory::new()->create(['price' => 50, 'vat' => 5, 'discount' => 5]);

    livewire(Pos::class)
        ->callTableAction('addToCart', $juice)
        ->callTableAction('addToCart', $juice)
        ->callTableAction('addToCart', $bread)
        ->callAction('checkoutAction', data: ['payment_method' => 'cash', 'paid_amount' => 270], arguments: ['total' => 270])
        ->assertHasNoActionErrors();

    $order = Order::query()->sole();

    expect($order->source)->toBe('pos')
        ->and($order->status)->toBe('paid')
        ->and($order->payment_method)->toBe('cash')
        ->and((float) $order->total)->toBe(270.0)
        ->and((float) $order->vat)->toBe(25.0)
        ->and((float) $order->discount)->toBe(5.0)
        ->and($order->ordersItems()->count())->toBe(2)
        ->and((float) $order->ordersItems()->where('product_id', $juice->id)->value('qty'))->toBe(2.0)
        ->and(Cart::query()->count())->toBe(0);

    // Without a selected customer the order goes to the shared POS account.
    expect($order->account->username)->toBe('pos');
});

it('checks out for the selected customer', function () {
    $customer = AccountFactory::new()->create(['name' => 'Ali Hassan']);
    $product = ProductFactory::new()->create();

    livewire(Pos::class)
        ->callTableAction('addToCart', $product)
        ->callAction('checkoutAction', data: ['account_id' => $customer->id, 'payment_method' => 'credit_card', 'paid_amount' => 110], arguments: ['total' => 110])
        ->assertHasNoActionErrors();

    $order = Order::query()->sole();

    expect($order->account_id)->toBe($customer->id)
        ->and($order->name)->toBe('Ali Hassan')
        ->and($order->ordersItems()->first()->account_id)->toBe($customer->id)
        ->and(Account::query()->where('username', 'pos')->exists())->toBeFalse();
});

it('applies a coupon code from the checkout form', function () {
    // The apply button used to read cart items from a form field that does not exist and failed.
    $coupon = CouponFactory::new()->create(['code' => 'FIXED10', 'type' => 'discount_coupon', 'amount' => 10]);
    $product = ProductFactory::new()->create(['price' => 100, 'vat' => 10, 'discount' => 0]);

    livewire(Pos::class)
        ->callTableAction('addToCart', $product)
        ->mountAction('checkoutAction', arguments: ['total' => 110])
        ->fillForm(['coupon' => 'FIXED10'])
        ->callAction(TestAction::make('apply')->schemaComponent('coupon'))
        ->assertSchemaStateSet(['coupon_id' => $coupon->id, 'paid_amount' => 100])
        ->assertNotified(trans('filament-ecommerce::messages.orders.actions.coupon.success'));
});

it('applies the coupon to the order total', function () {
    // The coupon used to be stored on the order without reducing its total.
    $coupon = CouponFactory::new()->create(['code' => 'FIXED10', 'type' => 'discount_coupon', 'amount' => 10]);
    $product = ProductFactory::new()->create(['price' => 100, 'vat' => 10, 'discount' => 0]);

    livewire(Pos::class)
        ->callTableAction('addToCart', $product)
        ->callAction('checkoutAction', data: ['payment_method' => 'cash', 'paid_amount' => 100, 'coupon_id' => $coupon->id], arguments: ['total' => 110])
        ->assertHasNoActionErrors();

    $order = Order::query()->sole();

    expect($order->coupon_id)->toBe($coupon->id)
        ->and((float) $order->total)->toBe(100.0)
        ->and((float) $order->discount)->toBe(10.0);
});
