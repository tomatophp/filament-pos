<?php

namespace TomatoPHP\FilamentPos;

use Illuminate\Support\ServiceProvider;


class FilamentPosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //Register generate command
        $this->commands([
           \TomatoPHP\FilamentPos\Console\FilamentPosInstall::class,
        ]);

        //Register Config file
        $this->mergeConfigFrom(__DIR__.'/../config/filament-pos.php', 'filament-pos');

        //Publish Config
        $this->publishes([
           __DIR__.'/../config/filament-pos.php' => config_path('filament-pos.php'),
        ], 'filament-pos-config');

        //Register Migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        //Publish Migrations
        $this->publishes([
           __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'filament-pos-migrations');
        //Register views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-pos');

        //Publish Views
        $this->publishes([
           __DIR__.'/../resources/views' => resource_path('views/vendor/filament-pos'),
        ], 'filament-pos-views');

        //Register Langs
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-pos');

        //Publish Lang
        $this->publishes([
           __DIR__.'/../resources/lang' => base_path('lang/vendor/filament-pos'),
        ], 'filament-pos-lang');

        //Register Routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

    }

    public function boot(): void
    {
        //you boot methods here
    }
}