<?php

namespace TomatoPHP\FilamentPos\Filament\Pages\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use TomatoPHP\FilamentEcommerce\Models\Cart;
use TomatoPHP\FilamentEcommerce\Models\Product;

trait HasCart
{
    /**
     * The cart rows of the current POS session.
     */
    protected function cartQuery(): Builder
    {
        return Cart::query()->where('session_id', $this->sessionID);
    }

    /**
     * @return Collection<int, Cart>
     */
    public function getCartItems(): Collection
    {
        return $this->cartQuery()->with('product')->get();
    }

    public function addToCart(Product $product): void
    {
        $existsOnCart = $this->cartQuery()
            ->where('product_id', $product->id)
            ->first();

        if (! $existsOnCart) {
            $this->cartQuery()->create([
                'session_id' => $this->sessionID,
                'item' => $product->name,
                'product_id' => $product->id,
                'price' => $product->price,
                'discount' => $product->discount,
                'vat' => $product->vat,
                'total' => ($product->price + $product->vat) - $product->discount,
                'qty' => 1,
            ]);

            return;
        }

        $existsOnCart->qty += 1;
        $existsOnCart->save();
    }

    public function removeFromCart(int $id): void
    {
        $this->cartQuery()->where('id', $id)->delete();
        $this->notify(trans('filament-pos::messages.notifications.delete.message'), 'success');
    }

    public function clearCart(): void
    {
        $this->cartQuery()->delete();
        $this->notify(trans('filament-pos::messages.notifications.clear.message'), 'success');
    }
}
