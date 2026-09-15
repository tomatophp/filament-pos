<?php

namespace TomatoPHP\FilamentPos\Filament\Widgets\Traits;

use Filament\Facades\Filament;
use Illuminate\Support\Str;

trait HasShield
{
    public static function canView(): bool
    {
        if (filament('filament-pos')->isShieldAllowed()) {
            return (bool) Filament::auth()->user()?->can(static::getPermissionName());
        }

        return true;
    }

    protected static function getPermissionName(): string
    {
        // filament-shield is optional; fall back to its default prefix when it is not installed.
        $shieldUtils = 'BezhanSalleh\\FilamentShield\\Support\\Utils';
        $prefix = class_exists($shieldUtils) ? $shieldUtils::getWidgetPermissionPrefix() : 'widget';

        return Str::of(class_basename(static::class))
            ->prepend($prefix.'_')
            ->toString();
    }
}
