# Translatable

[![Latest Version on Packagist](https://img.shields.io/packagist/v/javaabu/translatable.svg?style=flat-square)](https://packagist.org/packages/javaabu/translatable)
[![Test Status](../../actions/workflows/run-tests.yml/badge.svg)](../../actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/javaabu/translatable.svg?style=flat-square)](https://packagist.org/packages/javaabu/translatable)
![Code Coverage](./.github/coverage.svg)


## Introduction

Adds multi-lingual to Laravel models

To get started with this package, you can simply add `DbTranslatableSchema::columns($table);` or `JsonTranslatableSchema::columns($table);` to your migration `up` function.

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Javaabu\Translatable\DbTranslatable\DbTranslatableSchema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            ...

            DbTranslatableSchema::columns($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

And then all you need to do is add the `Translatable` implementation using the `IsDbTranslatable` or `IsJsonTranslatable` trait.

```php
...

class Post extends Model implements Translatable
{
    use IsDbTranslatable;

...
```

Now, your models will automatically be translated according to the current `app()->getLocale()`. To add different translations, all you need to do is

```php
// to add title for dv language
$post->title_dv = "Mee Dhivehi title eh";
```

## Documentation

You'll find the documentation on [https://docs.javaabu.com/docs/translatable](https://docs.javaabu.com/docs/translatable).

Find yourself stuck using the package? Found a bug? Do you have general questions or suggestions for improving this package? Feel free to create an [issue](../../issues) on GitHub, we'll try to address it as soon as possible.

If you've found a bug regarding security please mail [info@javaabu.com](mailto:info@javaabu.com) instead of using the issue tracker.


## Testing

You can run the tests with

``` bash
./vendor/bin/phpunit
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
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
