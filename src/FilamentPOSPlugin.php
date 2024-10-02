<?php

namespace TomatoPHP\FilamentPos;

use Filament\Contracts\Plugin;
use Filament\Panel;
use TomatoPHP\FilamentAccounts\FilamentAccountsPlugin;
use TomatoPHP\FilamentEcommerce\FilamentEcommercePlugin;
use TomatoPHP\FilamentPos\Filament\Pages\Pos;
use TomatoPHP\FilamentPos\Filament\Widgets\POSStateWidget;


class FilamentPOSPlugin implements Plugin
{
    public static bool $allowShield = false;

    public function getId(): string
    {
        return 'filament-pos';
    }


    public function allowShield(bool $allow = true): static
    {
        static::$allowShield = $allow;
        return $this;
    }

    public function isShieldAllowed(): bool
    {
        return static::$allowShield;
    }

    public function register(Panel $panel): void
    {
        $panel
            ->plugins([
                FilamentEcommercePlugin::make(),
                FilamentAccountsPlugin::make()
            ])
            ->pages([
                Pos::class,
            ])->widgets([
                POSStateWidget::class
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return new static();
    }
}
