![Screenshot](https://github.com/tomatophp/filament-pos/blob/master/arts/3x1io-tomato-pos.jpg)

# Filament POS

[![Latest Stable Version](https://poser.pugx.org/tomatophp/filament-pos/version.svg)](https://packagist.org/packages/tomatophp/filament-pos)
[![License](https://poser.pugx.org/tomatophp/filament-pos/license.svg)](https://packagist.org/packages/tomatophp/filament-pos)
[![Downloads](https://poser.pugx.org/tomatophp/filament-pos/d/total.svg)](https://packagist.org/packages/tomatophp/filament-pos)


POS System for FilamentPHP with a lot of features and integration with Ecommerce Builder

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


## Support

you can join our discord server to get support [TomatoPHP](https://discord.gg/Xqmt35Uh)

## Docs

you can check docs of this package on [Docs](https://docs.tomatophp.com/plugins/laravel-package-generator)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

Please see [SECURITY](SECURITY.md) for more information about security.

## Credits

- [Fady Mondy](mailto:info@3x1.io)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
