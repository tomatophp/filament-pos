<?php

namespace TomatoPHP\FilamentPos\Filament\Pages\Traits;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

trait HasShield
{
    public function booted(): void
    {
        if (! filament('filament-pos')->isShieldAllowed()) {
            return;
        }

        $this->beforeBooted();

        if (! static::canAccess()) {
            Notification::make()
                ->title(__('filament-shield::filament-shield.forbidden'))
                ->warning()
                ->send();

            $this->beforeShieldRedirects();

            redirect($this->getShieldRedirectPath());

            return;
        }

        $this->afterBooted();
    }

    protected function beforeBooted(): void {}

    protected function afterBooted(): void {}

    protected function beforeShieldRedirects(): void {}

    protected function getShieldRedirectPath(): string
    {
        return Filament::getUrl();
    }

    protected static function getPermissionName(): string
    {
        // filament-shield is optional; fall back to its default prefix when it is not installed.
        $shieldUtils = 'BezhanSalleh\\FilamentShield\\Support\\Utils';
        $prefix = class_exists($shieldUtils) ? $shieldUtils::getPagePermissionPrefix() : 'page';

        return Str::of(class_basename(static::class))
            ->prepend($prefix.'_')
            ->toString();
    }

    public static function canAccess(): bool
    {
        if (filament('filament-pos')->isShieldAllowed()) {
            return (bool) Filament::auth()->user()?->can(static::getPermissionName());
        }

        return true;
    }
}
