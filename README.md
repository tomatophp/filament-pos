![Screenshot](https://raw.githubusercontent.com/tomatophp/filament-pos/master/arts/3x1io-tomato-pos.jpg)

# Filament POS

[![Latest Stable Version](https://poser.pugx.org/tomatophp/filament-pos/version.svg)](https://packagist.org/packages/tomatophp/filament-pos)
[![License](https://poser.pugx.org/tomatophp/filament-pos/license.svg)](https://packagist.org/packages/tomatophp/filament-pos)
[![Downloads](https://poser.pugx.org/tomatophp/filament-pos/d/total.svg)](https://packagist.org/packages/tomatophp/filament-pos)


POS System for FilamentPHP with a lot of features and integration with Ecommerce Builder


## Screenshots 

![Home](https://raw.githubusercontent.com/tomatophp/filament-pos/master/arts/home.png)
![Cart](https://raw.githubusercontent.com/tomatophp/filament-pos/master/arts/cart.png)
![Checkout](https://raw.githubusercontent.com/tomatophp/filament-pos/master/arts/checkout.png)
![Notification](https://raw.githubusercontent.com/tomatophp/filament-pos/master/arts/notification.png)
![Print](https://raw.githubusercontent.com/tomatophp/filament-pos/master/arts/print.png)

## Installation

```bash
composer require tomatophp/filament-pos
```

we need the Media Library plugin to be installed and migrated you can use this command to publish the migration

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
```

now you need to install the settings hub use these commands

```bash
php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider" --tag="migrations"
php artisan filament-settings-hub:install
```

after install your package please run this command

```bash
php artisan filament-pos:install
```

finally register the plugin on `/app/Providers/Filament/AdminPanelProvider.php`

```php
->plugin(\TomatoPHP\FilamentPos\FilamentPOSPlugin::make())
```

## Use Filament Shield

you can use the shield to protect your resource and allow user roles by install it first

```bash
composer require bezhansalleh/filament-shield
```

Add the Spatie\Permission\Traits\HasRoles trait to your User model(s):

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    // ...
}
```
Publish the config file then setup your configuration:

```php
->plugin(\BezhanSalleh\FilamentShield\FilamentShieldPlugin::make())
```

Now run the following command to install shield:

```bash
php artisan shield:install
```

Now we can [publish the package assets]([https://github.com/bezhanSalleh/filament-shield](https://github.com/tomatophp/filament-users?tab=readme-ov-file#publish-assets)).

```bash
php artisan vendor:publish --tag="filament-users-config"
```

now you need to allow it on the plugin options

```php
->plugin(\TomatoPHP\FilamentPos\FilamentPOSPlugin::make()->allowShield())
```

for more information check the [Filament Shield](https://github.com/bezhanSalleh/filament-shield)

## Publish Assets

you can publish config file by use this command

```bash
php artisan vendor:publish --tag="filament-pos-config"
```

you can publish views file by use this command

```bash
php artisan vendor:publish --tag="filament-pos-views"
```

you can publish languages file by use this command

```bash
php artisan vendor:publish --tag="filament-pos-lang"
```

## Other Filament Packages

Checkout our [Awesome TomatoPHP](https://github.com/tomatophp/awesome)
