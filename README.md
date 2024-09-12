![Screenshot](https://raw.githubusercontent.com/tomatophp/filament-pos/blob/master/arts/3x1io-tomato-pos.jpg)

# Filament POS

[![Latest Stable Version](https://poser.pugx.org/tomatophp/filament-pos/version.svg)](https://packagist.org/packages/tomatophp/filament-pos)
[![License](https://poser.pugx.org/tomatophp/filament-pos/license.svg)](https://packagist.org/packages/tomatophp/filament-pos)
[![Downloads](https://poser.pugx.org/tomatophp/filament-pos/d/total.svg)](https://packagist.org/packages/tomatophp/filament-pos)


POS System for FilamentPHP with a lot of features and integration with Ecommerce Builder


## Screenshots 

![Home](https://raw.githubusercontent.com/tomatophp/filament-pos/blob/master/arts/home.png)
![Cart](https://raw.githubusercontent.com/tomatophp/filament-pos/blob/master/arts/cart.png)
![Checkout](https://raw.githubusercontent.com/tomatophp/filament-pos/blob/master/arts/checkout.png)
![Notification](https://raw.githubusercontent.com/tomatophp/filament-pos/blob/master/arts/notification.png)
![Print](https://raw.githubusercontent.com/tomatophp/filament-pos/blob/master/arts/print.png)

## Installation

```bash
composer require tomatophp/filament-pos
```
after install your package please run this command

```bash
php artisan filament-pos:install
```

finally register the plugin on `/app/Providers/Filament/AdminPanelProvider.php`

```php
->plugin(\TomatoPHP\FilamentPos\FilamentPOSPlugin::make())
```

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
