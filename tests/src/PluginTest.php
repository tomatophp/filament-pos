<?php

use Filament\Facades\Filament;
use TomatoPHP\FilamentPos\Filament\Pages\Pos;
use TomatoPHP\FilamentPos\Filament\Widgets\POSStateWidget;
use TomatoPHP\FilamentPos\FilamentPOSPlugin;
use TomatoPHP\FilamentPos\FilamentPosServiceProvider;

it('boots the service provider', function () {
    expect(app()->getProviders(FilamentPosServiceProvider::class))->not->toBeEmpty();
});

it('registers plugin', function () {
    $panel = Filament::getCurrentOrDefaultPanel();

    expect($panel->getPlugin('filament-pos'))->toBeInstanceOf(FilamentPOSPlugin::class);
});

it('registers the pos page and widget on the panel', function () {
    $panel = Filament::getCurrentOrDefaultPanel();

    expect($panel->getPages())->toContain(Pos::class)
        ->and($panel->getWidgets())->toContain(POSStateWidget::class);
});

it('keeps shield disabled by default', function () {
    expect(FilamentPOSPlugin::make()->isShieldAllowed())->toBeFalse();
});
