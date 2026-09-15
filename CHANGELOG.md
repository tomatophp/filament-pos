# Changelog

## v5.0.0

- Support Filament v5 (Livewire 4) and Laravel 12 / 13; requires `tomatophp/filament-ecommerce` ^5.0 and `tomatophp/filament-accounts` ^5.0.
- Ship `resources/dist/filament-pos.css` so the POS layout, cart and totals are styled in Filament v5 panels (run `php artisan filament:assets` after updating).
- The plugin only registers the ecommerce and accounts plugins when the panel does not already have them.
- Fix: the first visit to the POS page kept no cart session, so items went to a shared cart.
- Fix: applying a coupon in the checkout form failed, and a coupon on the order did not reduce its total.
- Fix: the POS widget counted orders from the same day of any month instead of today.
- Fix: `filament-shield` is optional again; the page and widget no longer need it installed.
- Add a Pest test suite.
