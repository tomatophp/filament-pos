<?php

namespace TomatoPHP\FilamentPos\Filament\Pages\Traits;

use Filament\Notifications\Notification;
use TomatoPHP\FilamentEcommerce\Models\Cart;

trait HasCart
{
    public function removeFromCart($id)
    {
        Cart::query()->where('id', $id)->delete();
        $this->notify(trans('filament-pos::messages.notifications.delete.message'), 'success');
    }


    public function clearCart()
    {
        Cart::query()->where('session_id', session()->get('sessionID'))->delete();
        $this->notify(trans('filament-pos::messages.notifications.clear.message'), 'success');
    }
}
