<x-filament-panels::page>
    @livewire(\TomatoPHP\FilamentPos\Filament\Widgets\POSStateWidget::class)
    <div class="grid sm:grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            {{ $this->table }}
        </div>
       <div class="flex flex-col gap-4">
           <x-filament::section :heading="trans('filament-pos::messages.view.cart')">
               @php $cart = \TomatoPHP\FilamentEcommerce\Models\Cart::query()->where('session_id', $this->sessionID)->get() @endphp
               @if(count($cart))
                   <div class="divide-y divide-gray-100 dark:divide-white/5">
                       @foreach($cart as $item)
                           <div class="flex justify-between items-center py-2">
                               <div>
                                   <p>{{ $item->product->name }}</p>
                                   <p>{{ $item->qty }} * {{ number_format(($item->price+$item->vat)-$item->discount, 2) }}<small>{{ setting('site_currency') }}</small></p>
                               </div>
                               <div>
                                   <x-filament::icon-button :tooltip="trans('filament-pos::messages.view.remove')" icon="heroicon-s-trash" color="danger" wire:click="removeFromCart({{ $item->id }})"></x-filament::icon-button>
                               </div>
                           </div>
                       @endforeach
                   </div>
                   <div class="mt-2">
                       <x-filament::button color="danger" wire:click="clearCart">{{ trans('filament-pos::messages.view.clear') }}</x-filament::button>
                   </div>
               @else
                   <div class="text-center flex justify-center items-center flex-col h-full">
                       <div class="flex justify-center items-center flex-col gap-2">
                           <x-heroicon-c-shopping-cart class="w-8 h-8" />
                           <p>{{ trans('filament-pos::messages.view.empty') }}</p>
                       </div>
                   </div>
               @endif
           </x-filament::section>
           @if(count($cart))
                <x-filament::section :heading="trans('filament-pos::messages.view.totals')">
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                     <div class="flex justify-between items-center py-2">
                          <p class="font-bold">{{ trans('filament-pos::messages.view.subtotal') }}</p>
                          <p>{{ number_format($cart->sum(function ($item){
                                return $item->qty * $item->price;
                            }), 2) }}<small>{{ setting('site_currency') }}</small></p>
                     </div>
                    <div class="flex justify-between items-center py-2 text-danger-600">
                        <p class="font-bold">{{ trans('filament-pos::messages.view.discount') }}</p>
                        <p>{{ number_format($cart->sum(function ($item){
                                return $item->qty * $item->discount;
                            }), 2) }}<small>{{ setting('site_currency') }}</small></p>
                    </div>
                     <div class="flex justify-between items-center py-2">
                          <p class="font-bold">{{ trans('filament-pos::messages.view.vat') }}</p>
                          <p>{{ number_format($cart->sum(function ($item){
                                return $item->qty * $item->vat;
                            }), 2) }}<small>{{ setting('site_currency') }}</small></p>
                     </div>
                     <div class="flex justify-between items-center py-2">
                          <p class="font-bold">{{ trans('filament-pos::messages.view.total') }}</p>
                          <p class="font-bold">{{ number_format($cart->sum(function ($item){
                                return $item->qty * $item->total;
                            }), 2) }}<small>{{ setting('site_currency') }}</small></p>
                     </div>
                </div>
                <div class="mt-2">
                     {{ ($this->checkoutAction)(['total' => $cart->sum(fn($item) => ($item->qty * $item->total))]) }}
                </div>
           </x-filament::section>
           @endif
       </div>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
