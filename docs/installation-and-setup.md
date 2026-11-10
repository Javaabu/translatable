---
title: Installation & Setup
sidebar_position: 1.2
---

# Installation & Setup

## 1. Install

```bash
composer require javaabu/translatable
```

The service provider is discovered automatically.

## 2. Create the languages table

```bash
php artisan vendor:publish --tag="translatable-migrations"
php artisan migrate
```

## 3. Add your languages

```bash
php artisan translatable:add-languages en dv
```

This adds English and Dhivehi from built-in presets (name, own name, direction, flag). Codes without a preset get a minimal row you can edit. Only **active** languages can be translated to, and the default locale (`en` unless you change `translatable.default_locale`) must be one of them. See [Languages](basic-usage/languages.md).

## 4. Make a model translatable

Add the columns:

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('body');
    $table->string('slug');

    $table->jsonTranslatable(); // translations (JSON) and lang
    // or: $table->dbTranslatable(); // translatable_parent_id and lang

    $table->timestamps();
});
```

And the trait:

```php
use Illuminate\Database\Eloquent\Model;
use Javaabu\Translatable\Contracts\JsonTranslatable;
use Javaabu\Translatable\JsonTranslatable\IsJsonTranslatable;

class Post extends Model implements JsonTranslatable
{
    use IsJsonTranslatable;

    protected $fillable = ['title', 'body', 'slug'];

    protected array $translatable = ['title', 'body'];
}
```

See [Making models translatable](basic-usage/making-models-translatable.md) for the DB strategy and the options.

## 5. Set the locale per request

Add the `language` middleware to routes whose locale comes from the URL, the `?lang=` query or the session:

```php
Route::middleware(['web', 'language'])->prefix('{language}')->group(function () {
    Route::get('posts/{post}', [PostsController::class, 'show'])->name('posts.show');
});
```

See [Locales and URLs](basic-usage/locales-and-urls.md).

## Publishing (optional)

| Tag | What |
| --- | --- |
| `translatable-config` | `config/translatable.php` ([Configuration](advanced-usage/configuration.md)) |
| `translatable-migrations` | The languages table migrations |
| `translatable-views` | The Blade views, to `resources/views/vendor/translatable` |
| `translatable-flags` | Flag images, to `public/vendors/flags` |

```bash
php artisan vendor:publish --tag="translatable-config"
```
