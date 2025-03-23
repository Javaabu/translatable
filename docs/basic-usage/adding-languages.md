---
title: Adding Languages
description: How to add and remove locales allowed for translations
sidebar_position: 10
---

Translatables will only work once allowed languages are added.

# Adding an allowed language

To add a language that is allowed to translate to, just simply add to the model that is used in `language_model` in your `config/translatable.php` file. The code below will work if the `language_model` is kept as it's default value.

```php
use Javaabu\Translatable\Models\Language;

...

Language::create([
    'name' => 'English',
    'code' => 'en',
    'locale' => 'en',
    'flag' => '🇬🇧',
    'is_rtl' => false,
    'active' => true,
]);
```

The `code` field will be used internally while the `locale` field will be what is set to the html `lang` attribute. The `is_rtl` attribute controls whether this language is meant to be shown right-to-left.

:::warning

If your `code` contains an underscore (`_`), using the lang suffixes (`attr_en`) may not work as expected, please use a `-` if necessary.

:::

:::danger

When adding allowed languages, please note that by default, the `default_locale` will not be allowed as no `language_model` record exists for it. Ensure you have added the `default_locale` language for translatables to work as intended.

:::

# Deleting an allowed language

To soft-delete a language, you can simply set the `active` to false.

```php
$lang_en = \Javaabu\Translatable\Facades\Languages::get('en');

$lang_en->active = false;
$lang_en->save();
```

To completely remove a language, you can delete the language record. 

:::warning

Note that this does not delete the existing language records on a translatable model.

:::

```php
\Javaabu\Translatable\Facades\Languages::get('en')->delete();
```
