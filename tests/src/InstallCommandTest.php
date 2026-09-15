<?php

use TomatoPHP\FilamentPos\Console\FilamentPosInstall;

use function Pest\Laravel\artisan;

it('runs the install command', function () {
    // The real command shells out to `php artisan migrate`; skip that sub-process in tests.
    app()->bind(FilamentPosInstall::class, fn () => new class extends FilamentPosInstall
    {
        public array $ran = [];

        public function artisanCommand(array $command, ?bool $withOutput = false): void
        {
            $this->ran[] = $command;
        }
    });

    artisan('filament-pos:install')
        ->expectsOutputToContain('Filament POS installed successfully.')
        ->assertSuccessful();
});
