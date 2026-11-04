---
title: Upgrading from v2
sidebar_position: 1.25
---

v3 keeps v2's storage (the `languages` table, the JSON and DB columns) and its API. Most apps only need the steps in **Required**.

# Required

## Versions

PHP 8.4 and Laravel 12 or 13.

```bash
composer require javaabu/translatable:^3.0
```

## The languages table

v3 adds `languages.local_name` (the language's own name, ދިވެހި). Publish the migrations and run the new one; it does nothing if the column is already there:

```bash
php artisan vendor:publish --tag="translatable-migrations"
php artisan migrate
```

Then fill it in, or re-add languages from the presets: `php artisan translatable:add-languages` skips existing ones, so update them by hand (`Languages::get('dv')->update(['local_name' => 'ދިވެހި'])`).

## Config

`styles` is gone (the components follow javaabu/forms). New keys: `enabled`, `thaana_locales`, `views`, `inertia`. If you published the config, remove `styles` and copy the new keys from the package's `config/translatable.php`, or delete your copy to use the defaults. See [Configuration](advanced-usage/configuration.md).

## Translations set through attributes are saved with the model

In v2, `$post->title_dv = '…'` saved the translation immediately (and, on an unsaved model, failed or created a stray row). In v3 it's staged like any attribute and written on `save()`:

```php
// v2
$post->title_dv = 'މަރުޙަބާ';

// v3
$post->title_dv = 'މަރުޙަބާ';
$post->save();
```

The same goes for `fill()`, `create()` and `update()` with suffixed names, and for writing a translatable attribute in another locale (`$post->title = …` while the locale is `dv`). `addTranslation()` and `addTranslations()` still save immediately.

## javaabu/helpers is no longer required

v2 required javaabu/helpers, which requires javaabu/settings v1, so translatable couldn't be installed next to javaabu/settings v2. v3 doesn't need helpers (or settings).

The only thing that came from helpers was the `Language` model implementing helpers' `AdminModel` (`admin_link`, `logging_name`, `log_url`, `canViewAdminLink()`, …). `Language` keeps `admin_url`, `admin_link_name` and the `search()` scope without helpers. If your app uses the rest (for example an admin page or activity log that type-hints `AdminModel`), install helpers and use the `AdminLanguage` model, which is `Language` with helpers' `AdminModel`:

```bash
composer require javaabu/helpers
```

```php
// config/translatable.php
'language_model' => Javaabu\Translatable\Models\AdminLanguage::class,
```

If you extended `Language` in your app, add `implements \Javaabu\Helpers\AdminModel\AdminModel` and `use \Javaabu\Helpers\AdminModel\IsAdminModel;` to your model instead (or extend `AdminLanguage`). Both use the `languages` table and keep `Language` as their morph class.

## The table components need javaabu/forms v2

`<x-translatable::titles>` and `<x-translatable::table-cells>` are rebuilt on javaabu/forms v2 components, with Lucide icons instead of Font Awesome classes. Install `javaabu/forms:^2.0`. If you published the views, delete `resources/views/vendor/translatable` and publish again: the views moved to `common/`.

# Behaviour changes

- **Rows without a `lang`** are read as `translatable.default_locale`, not the current request's locale. They get that `lang` the next time an attribute is set on them.
- **Writing in a locale that isn't an active language** (the app locale is `fr` but there's no `fr` language) writes the model's own columns instead of throwing `LanguageNotAllowedException`.
- **`fill()` on DB models** accepts the suffixed names of fillable translatable fields (`title_dv`), as JSON models already did. If your forms post such names to a DB model and relied on them being ignored, remove them.
- **`addTranslation()`** takes any value (`mixed $value`), so you can clear a translation with `null`. If you implement `Contracts\Translatable` yourself, update the signature.
- **JSON models cast `translations` to an array** themselves; your own cast still wins.
- **The languages list** is cached once per request, an empty list isn't cached, it's ordered by id, and saving, deleting or restoring a language clears the cache. A custom language model that overrides `booted()` must call `parent::booted()` to keep that.
- **The current locale** (`Languages::setCurrentLocale()`) is per request (a scoped binding), so it no longer leaks between Octane requests.
- **The language middleware** keeps the locale in the session only when the request has one and isn't an API request. Without javaabu/helpers' `is_api_request()`, API requests are those under `app.api_prefix` (`api`).
- **Language subclasses** use the `languages` table (`Language::$table` is set), whatever the subclass is called.
- **Fixed:** `$model->url()` read `translatable.default_redirect` instead of `translatable.default_redirect_route`.

# New

- Models can list their fields in `protected array $translatable` (what the v2 docs described) instead of overriding `getTranslatables()`.
- `Translatable::rules()`, `values()`, `inputs()`, `status()`, `locales()` and the rest of the [contract for package authors](advanced-usage/for-package-authors.md).
- [Blade components](basic-usage/blade-components.md): per-locale fields, language switcher, translation status, add-translation notice.
- [React components](basic-usage/react-components.md) through javaabu/manifest.
- `php artisan translatable:add-languages`, and [`InteractsWithLanguages`](advanced-usage/testing.md) for tests.
