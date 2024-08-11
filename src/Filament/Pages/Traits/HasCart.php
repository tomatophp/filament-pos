<?php

namespace TomatoPHP\FilamentPos\Filament\Pages\Traits;

use Filament\Notifications\Notification;
use TomatoPHP\FilamentEcommerce\Models\Cart;

trait HasCart
{
    public function removeFromCart($id)
    {
        Cart::query()->where('id', $id)->delete();

        
        $this->notify('Item removed from cart', 'success');
        Notification::make()
            ->title('Success')
            ->body('Item removed from cart')
            ->success();
    }


    public function clearCart()
    {
        Cart::query()->where('session_id', session()->get('sessionID'))->delete();

        $this->notify('Cart Cleared', 'success');
    }
}
