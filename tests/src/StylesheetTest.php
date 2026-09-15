<?php

use Filament\Support\Facades\FilamentAsset;

/**
 * Filament v5 panels only compile the utilities Filament itself uses, so every utility class the
 * POS views rely on must be defined in the stylesheet the package ships.
 */
it('registers the pos stylesheet with filament', function () {
    $styles = collect(FilamentAsset::getStyles(['tomatophp/filament-pos']))
        ->map(fn ($asset) => $asset->getId());

    expect($styles)->toContain('filament-pos');
});

it('ships unlayered rules so the utilities outrank the panel reset', function () {
    // Registered package styles load before Filament's app.css; a cascade layer declared here would
    // rank below Filament's base layer and its reset would strip spacing and text sizes.
    $css = file_get_contents(__DIR__.'/../../resources/dist/filament-pos.css');

    expect($css)->not->toContain('@layer');
});

it('ships every utility class the pos views use', function () {
    $css = file_get_contents(__DIR__.'/../../resources/dist/filament-pos.css');

    // Selectors like `.md\:col-span-2 {`, `.divide-y >` or `.dark\:divide-white\/5 >`.
    $defined = collect(preg_match_all('/\.((?:[a-z0-9-]|\\\\[:\/\[\]])+)(?=[\s{,:>])/i', $css, $matches) ? $matches[1] : [])
        ->map(fn (string $selector) => stripslashes($selector))
        ->all();

    // Filament component classes are styled by Filament itself.
    $ignored = '/^fi-/';

    $used = collect(glob(__DIR__.'/../../resources/views/{,*/}*.blade.php', GLOB_BRACE))
        ->flatMap(function (string $view) {
            preg_match_all('/class="([^"]*)"/', file_get_contents($view), $found);

            return collect($found[1])->flatMap(fn (string $classes) => preg_split('/\s+/', $classes));
        })
        ->reject(fn (string $class) => $class === '' || preg_match('/[{}@$]/', $class))
        ->reject(fn (string $class) => preg_match($ignored, $class))
        ->unique()
        ->values();

    expect($used)->not->toBeEmpty()
        ->and($used->diff($defined)->values()->all())->toBe([]);
});
