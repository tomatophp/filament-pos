<?php

namespace TomatoPHP\FilamentPos;

use Filament\Contracts\Plugin;
use Filament\Panel;


class FilamentPOSPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-pos';
    }

    public function register(Panel $panel): void
    {
        //
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
