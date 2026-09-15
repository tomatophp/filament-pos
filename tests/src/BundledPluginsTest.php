<?php

use Filament\Panel;
use TomatoPHP\FilamentAccounts\FilamentAccountsPlugin;
use TomatoPHP\FilamentEcommerce\FilamentEcommercePlugin;
use TomatoPHP\FilamentPos\FilamentPOSPlugin;

/**
 * The POS plugin bundles the ecommerce and accounts plugins. A host panel that already registered and
 * configured one of them must keep its own instance and options.
 */
it('keeps the host panel configuration of plugins it bundles', function () {
    $ecommerce = FilamentEcommercePlugin::make();
    $accounts = FilamentAccountsPlugin::make();

    $panel = Panel::make()
        ->id('pos-bundled-plugins-host')
        ->plugin($accounts)
        ->plugin($ecommerce)
        ->plugin(FilamentPOSPlugin::make());

    expect($panel->getPlugin('filament-ecommerce'))->toBe($ecommerce)
        ->and($panel->getPlugin('filament-accounts'))->toBe($accounts);
});

it('registers the bundled plugins when the panel does not have them', function () {
    $panel = Panel::make()
        ->id('pos-bundled-plugins-empty')
        ->plugin(FilamentPOSPlugin::make());

    expect($panel->hasPlugin('filament-ecommerce'))->toBeTrue()
        ->and($panel->hasPlugin('filament-accounts'))->toBeTrue();
});
