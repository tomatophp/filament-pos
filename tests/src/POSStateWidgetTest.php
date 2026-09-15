<?php

use TomatoPHP\FilamentEcommerce\Database\Factories\OrderFactory;
use TomatoPHP\FilamentPos\Filament\Widgets\POSStateWidget;
use TomatoPHP\FilamentPos\Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());
    seedOrderTypes();
});

it('renders the pos state widget', function () {
    OrderFactory::new()->create(['source' => 'pos', 'total' => 150]);

    livewire(POSStateWidget::class)
        ->assertSuccessful()
        ->assertSee('Total Orders Today')
        ->assertSee('150.00');
});

it('only counts pos orders created today', function () {
    OrderFactory::new()->create(['source' => 'pos', 'total' => 100]);
    OrderFactory::new()->create(['source' => 'system', 'total' => 40]);
    // Same day of the month, one year earlier: it used to be counted because the query matched the day only.
    OrderFactory::new()->create(['source' => 'pos', 'total' => 70, 'created_at' => now()->subYearNoOverflow()]);

    $stats = (fn () => $this->getStats())->call(new POSStateWidget);

    expect((string) $stats[0]->getValue())->toBe('1')
        ->and($stats[1]->getValue())->toBe('100.00');
});
