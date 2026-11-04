# Translatable

[![Tests](https://github.com/n4ff4h/translatable/actions/workflows/tests.yml/badge.svg?branch=next-gen)](https://github.com/n4ff4h/translatable/actions/workflows/tests.yml?query=branch%3Anext-gen)
[![codecov](https://codecov.io/gh/n4ff4h/translatable/branch/next-gen/graph/badge.svg)](https://codecov.io/gh/n4ff4h/translatable/tree/next-gen)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/javaabu/translatable.svg)](https://packagist.org/packages/javaabu/translatable)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE.md)

Multilingual Eloquent models for Laravel. Attributes are read in the current locale, every locale is readable and writable as `title_dv`, and translations are stored as JSON on the row or as a row per locale. Ships Blade components ([javaabu/forms](https://github.com/Javaabu/forms) v2) and Inertia React components ([@javaabu/forms](https://github.com/Javaabu/react-forms)), with Dhivehi and RTL built in.

```php
class Post extends Model implements JsonTranslatable
{
    use IsJsonTranslatable;

    protected array $translatable = ['title', 'body'];
}

$post = Post::create(['title_en' => 'Welcome', 'title_dv' => 'މަރުޙަބާ']);

app()->setLocale('dv');
$post->title;    // މަރުޙަބާ
$post->title_en; // Welcome
```

```blade
<x-forms::form :model="$post">
    <x-translatable::text name="title" :label="__('Title')" required />
</x-forms::form>
```

## Installation

```bash
composer require javaabu/translatable
php artisan vendor:publish --tag="translatable-migrations"
php artisan migrate
php artisan translatable:add-languages en dv
```

Upgrading from v2? See [docs/upgrading.md](docs/upgrading.md).

## Documentation

You'll find the documentation on [https://docs.javaabu.com/translatable](https://docs.javaabu.com/translatable), and in [`docs/`](docs/intro.md). Package authors adding optional translation support: [docs/advanced-usage/for-package-authors.md](docs/advanced-usage/for-package-authors.md). AI assistants: [llms.txt](llms.txt).

Find yourself stuck using the package? Found a bug? Do you have general questions or suggestions for improving this package? Feel free to create an [issue](../../issues) on GitHub, we'll try to address it as soon as possible.

If you've found a bug regarding security please mail [info@javaabu.com](mailto:info@javaabu.com) instead of using the issue tracker.

## Testing

```bash
composer test
composer lint
pnpm install && pnpm check
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email [info@javaabu.com](mailto:info@javaabu.com) instead of using the issue tracker.

## Credits

- [Javaabu Pvt. Ltd.](https://github.com/javaabu)
- [Arushad Ahmed (@dash8x)](http://arushad.com)
- [Xylam (@Xylam)](https://github.com/Xylam)
- [FlameXode (@WovenCoast)](https://github.com/WovenCoast)
- [Athfan Khaleel (@athphane)](https://athfan.com)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
