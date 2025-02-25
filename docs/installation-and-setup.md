---
title: Installation & Setup
sidebar_position: 1.2
---

# Installation

You can install the package via composer:

```bash
composer require javaabu/translatable
```

# Publishing the config file

Publishing the config file is optional:

```bash
php artisan vendor:publish --provider="Javaabu\Translatable\TranslatableServiceProvider" --tag="translatable-config"
```

This is the default content of the config file:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Some config option
    |--------------------------------------------------------------------------
    |
    | Give a description of what each config option is like this
    |
    */

    'fields_ignored_for_translation' => [
        'id',
        'lang',
        'created_at',
        'updated_at',
        'deleted_at',
    ],
    'allowed_translation_locales' => [
        'en' => 'English',
        'dv' => 'Dhivehi',
        'jp' => 'Japanese',
    ],
    'lang_suffix_should_fallback' => false,
];


```

# Setup


Translatables currently provides **two** different types of translatables, `Db` and `Json`. Check out [Difference between DB and JSON translatable](./basic-usage/difference-isdbtranslatable-isjsontranslatable.md) to learn the differences and design considerations for both

## Setting up your migrations

If you are setting up a new model, you can simply add either `$table->dbTranslatable();` or `$table->jsonTranslatable();` into your migration schema create function.

:::warning

Use one or the other, **DON'T use both at the same time**.

:::

```php
use Javaabu\Translatable\DbTranslatable\DbTranslatableSchema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // ...

            $table->dbTranslatable();
            // OR
            $table->jsonTranslatable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

Or if you have already made the model, you can write a migration to add the columns to the existing table.

```php
use Javaabu\Translatable\DbTranslatable\DbTranslatableSchema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->dbTranslatable();
            // OR
            $table->jsonTranslatable();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropDbTranslatable();
            // OR
            $table->dropJsonTranslatable();
        });
    }
};
```

## Setting up your models


All you need to do is add the `Translatable` implementation using the `IsDbTranslatable` or `IsJsonTranslatable` trait.

```php
...
use Javaabu\Translatable\DbTranslatable\IsDbTranslatable;
use Javaabu\Translatable\Translatable;

class Post extends Model implements Translatable
{
    use IsDbTranslatable;
    // OR
    use IsJsonTranslatable;

...
```

Once this is setup, you are good to go!
