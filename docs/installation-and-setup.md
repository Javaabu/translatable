---
title: Installation & Setup
sidebar_position: 1.2
---

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
