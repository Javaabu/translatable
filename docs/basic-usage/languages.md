---
title: Languages
description: Adding, switching off and reading the languages models can be translated into
sidebar_position: 1
---

The languages models can be translated into are rows of the `languages` table. Only active ones count.

# Adding languages

```bash
php artisan translatable:add-languages en dv ar
php artisan translatable:add-languages fr --inactive
```

With no codes it adds the default locale and `dv`. Presets exist for `en`, `dv`, `ar`, `zh`, `ja`, `hi`, `ru`, `de`, `fr`, `it` and `es`. Languages that are already in the table are skipped.

Or create them yourself (the model is `translatable.language_model`):

```php
use Javaabu\Translatable\Models\Language;

Language::create([
    'name'       => 'Dhivehi',
    'local_name' => 'ދިވެހި',
    'code'       => 'dv',
    'locale'     => 'dv',
    'flag'       => 'mv',
    'is_rtl'     => true,
    'active'     => true,
]);
```

| Column | What |
| --- | --- |
| `code` | Used everywhere in the package: suffixes (`title_dv`), the `lang` column, URLs. Don't use `_` in it. |
| `locale` | The HTML `lang` attribute |
| `name` | The English name |
| `local_name` | The language's own name (ދިވެހި), shown by the language switcher and tabs. Falls back to `name`. |
| `flag` | A flag code (`mv`); `$language->flag_url` gives `vendors/flags/mv.svg` (publish `translatable-flags`) |
| `is_rtl` | Right-to-left |
| `active` | Can be translated into |

The list is cached (`translatable.cache`). Saving, deleting or restoring a language clears the cache.

# Switching a language off

```php
use Javaabu\Translatable\Facades\Languages;

Languages::get('ar')->update(['active' => false]);
```

Existing translations stay in the database; they just aren't read or offered any more. Deleting a language does the same.

# Admin pages

`Language` has `admin_url` (the `admin.languages.show` route), `admin_link_name` and a `search()` scope over `name`, `local_name`, `code`, `locale` and `flag`:

```php
Language::search($request->input('search'))->paginate();
```

If your admin pages or activity log need javaabu/helpers' `AdminModel` (`admin_link`, `logging_name`, `log_url`, …), install javaabu/helpers and use the `AdminLanguage` model:

```php
// config/translatable.php
'language_model' => Javaabu\Translatable\Models\AdminLanguage::class,
```

translatable itself doesn't need helpers.

# Reading the languages

```php
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Translatable;

Languages::all();                 // active Language models
Languages::get('dv');             // one, or null
Languages::currentLanguageCode(); // the locale of this request
Languages::isRtl('dv');           // true
Languages::allExceptCurrent();

Translatable::locales();          // Locale objects, the default first
Translatable::locale('dv')->native_name; // ދިވެހި
```

`Translatable::locales()` gives `Locale` value objects (`code`, `locale`, `name`, `native_name`, `direction`, `is_default`, `flag_url`, `isRtl()`, `isThaana()`, `toArray()`), which is what the views and other packages use.
