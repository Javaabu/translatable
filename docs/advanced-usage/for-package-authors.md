---
title: For package authors
sidebar_position: 1
---

Many Javaabu packages (permissions, settings, notifications, locations, meta, menus, widgets, form-builder, post-types, cms, …) let apps translate their models **when javaabu/translatable is installed**, and work exactly as before when it isn't. This page is the contract they build on. Everything here is covered by semver: it doesn't change within v3.

The example is a `javaabu/locations` package whose `Island` names can be translated (`Malé` / `މާލެ`).

# composer.json

Translatable is optional: suggest it, and install it for your tests.

```json
{
    "suggest": {
        "javaabu/translatable": "Translate island names (^3.0)"
    },
    "require-dev": {
        "javaabu/translatable": "^3.0@dev"
    },
    "repositories": [
        { "type": "vcs", "url": "https://github.com/n4ff4h/translatable" }
    ]
}
```

Drop the repository once v3 is on Packagist.

# Detecting translatable

Never call a translatable class without checking that it exists. Put the check in one place in your package:

```php
namespace Javaabu\Locations\Support;

use Javaabu\Translatable\Translatable;

class Translations
{
    public static function enabled(): bool
    {
        return class_exists(Translatable::class) && Translatable::enabled();
    }

    public static function supports(object|string|null $model, ?string $field = null): bool
    {
        return class_exists(Translatable::class) && Translatable::supports($model, $field);
    }
}
```

- `Translatable::enabled()` is `true` when `translatable.enabled` is on and at least one language is active.
- `Translatable::supports($model, $field = null)` is `true` when it's enabled, the model (an instance or a class name) implements `Javaabu\Translatable\Contracts\Translatable`, and the field (if given) is one of its translatable fields.
- `$model instanceof \Javaabu\Translatable\Contracts\Translatable` is safe even when the package isn't installed: PHP returns `false` for an unknown class.

# Making your model translatable

Translations are opt-in per model. Use the **JSON** storage for package models: it adds two columns and keeps one row per record, so ids, routes and relations don't change.

## 1. Add the columns

Your migration runs whether or not translatable is installed, so add the columns yourself, both nullable:

```php
Schema::create('islands', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    // ...

    // Translations (javaabu/translatable). Harmless without it.
    $table->json('translations')->nullable();
    $table->string('lang')->nullable()->index();

    $table->timestamps();
});
```

Rows saved before translatable was installed have no `lang`; they're read as the default locale (`translatable.default_locale`).

## 2. Let the model be swapped

Your package resolves its model class from config, as every next-gen package does:

```php
// config/locations.php
'models' => [
    'island' => Javaabu\Locations\Models\Island::class,
],
```

Then pick one of these. Both are fine; the first needs nothing from your package.

### The app opts in

The app extends your model, adds the trait, and points your config at it:

```php
namespace App\Models;

use Javaabu\Locations\Models\Island as BaseIsland;
use Javaabu\Translatable\Contracts\JsonTranslatable;
use Javaabu\Translatable\JsonTranslatable\IsJsonTranslatable;

class Island extends BaseIsland implements JsonTranslatable
{
    use IsJsonTranslatable;

    protected array $translatable = ['name'];
}
```

```php
// config/locations.php
'models' => ['island' => App\Models\Island::class],
```

### Your package ships the translatable model

Put a subclass next to your model. PHP only loads it when it's referenced, so it's safe to ship even when translatable isn't installed:

```php
namespace Javaabu\Locations\Models\Translatable;

use Javaabu\Locations\Models\Island as BaseIsland;
use Javaabu\Translatable\Contracts\JsonTranslatable;
use Javaabu\Translatable\JsonTranslatable\IsJsonTranslatable;

class Island extends BaseIsland implements JsonTranslatable
{
    use IsJsonTranslatable;

    protected array $translatable = ['name'];

    // Keep morph types pointing at the base model
    public function getMorphClass(): string
    {
        return BaseIsland::class;
    }
}
```

If the base model uses `HasFactory`, override `newFactory()` in the subclass to return the base model's factory, and register policies for both classes.

Choose it when the app hasn't configured a model:

```php
// LocationsServiceProvider::register()
$this->app->bind('locations.models.island', function () {
    return config('locations.models.island')
        ?? (Translations::enabled() ? Models\Translatable\Island::class : Models\Island::class);
});
```

`Translations::enabled()` reads the languages table, so call it lazily (in a binding or when the model is first needed), not while providers are registering.

# Forms

A translatable field has one input per active locale, named `{field}_{locale}`, the default locale included: `name_en`, `name_dv`. Validation, old input, model binding and `fill()` all use these names.

## Validation

Expand your rules with `Translatable::rules()`. The model's primary locale keeps the rules you wrote; the others get `nullable` instead of `required`:

```php
public function rules(): array
{
    $rules = [
        'name' => ['required', 'string', 'max:255'],
        'atoll_id' => ['required', 'exists:atolls,id'],
    ];

    $island = $this->route('island') ?? config('locations.models.island');

    if (Translations::supports($island)) {
        $rules = Translatable::rules($island, $rules);
        // ['name_en' => ['required', 'string', 'max:255'], 'name_dv' => ['nullable', 'string', 'max:255'], 'atoll_id' => [...]]
    }

    return $rules;
}
```

Pass `require_all: true` to require every locale. Fields that aren't translatable pass through unchanged.

## Saving

`fill()`, `create()` and `update()` accept the per-locale names, and the translations are written when the model is saved:

```php
$island = Island::create($request->validated()); // name_en and name_dv
$island->update($request->validated());
```

## Rendering (Blade)

Use translatable's components when the field is translatable, and your usual component otherwise:

```blade
@if(\Javaabu\Locations\Support\Translations::supports($island, 'name'))
    <x-translatable::text name="name" :label="__('Name')" required />
@else
    <x-forms::text name="name" :label="__('Name')" required />
@endif
```

`<x-translatable::text>` renders one `<x-forms::text>` per locale (with `dir`, `lang` and the Thaana keyboard for Dhivehi), in tabs. See [Blade components](../basic-usage/blade-components.md).

## Rendering (Inertia React)

Send the inputs and values with the page; don't import translatable's React components from your package, because they only exist in apps that install it:

```php
return Inertia::render('locations::Islands/Edit', [
    'island' => $island,
    'translations' => Translations::supports($island) ? [
        'values' => Translatable::values($island), // ['name_en' => 'Malé', 'name_dv' => 'މާލެ']
        'inputs' => ['name' => Translatable::inputs('name', $island)->values()],
    ] : null,
]);
```

Each input serializes to `{ field, name, locale, direction, thaana, primary, language: { code, name, native_name, … } }`, enough to render one `@javaabu/forms` `<Text>` per locale with `dir`, `thaana` and a label. Apps that install translatable can use its `TranslatableText` component, which takes the same data. See [React components](../basic-usage/react-components.md).

## Stencil fields

javaabu/stencil fields call `->translatable()`. When `Translatable::supports($model, $field)` is true, the field expands into the per-locale inputs from `Translatable::inputs()`; otherwise it renders as a plain field. Both of stencil's drivers use the same data, so Blade and React forms match.

# Reading translations

| Call | Gives |
| --- | --- |
| `$island->name` | The name in the current locale, falling back to the model's own value |
| `$island->name_dv` | The Dhivehi name, or `null` (see `lang_suffix_should_fallback`) |
| `$island->translate('name', 'dv', fallback: false)` | The same, explicitly |
| `Translatable::values($island)` | Every translatable field in every locale, keyed by input name |
| `Translatable::status($island)` | Which locales the model has, for status badges |
| `$island->toArray()` | Translatable fields in the current locale |

For an API resource, merge `Translatable::values($island)` when your clients need every locale.

# API reference

All static, on `Javaabu\Translatable\Translatable`:

| Method | Returns |
| --- | --- |
| `enabled()` | `bool` |
| `supports(object\|string\|null $model, ?string $field = null)` | `bool` |
| `isDbTranslatable(object\|string\|null $model)` | `bool`: the model keeps each locale in its own row |
| `locales()` | `Collection<int, Locale>`, the default first |
| `locale(?string $code = null)` | `?Locale`, the current one by default |
| `defaultLocale()` / `currentLocale()` | `string` |
| `primaryLocale(object\|string\|null $model = null)` | `string`: the model's `lang`, or the current locale for a new model |
| `inputName(string $field, string $locale)` | `string`: `name_dv` |
| `inputs(string $field, object\|string\|null $model = null)` | `Collection<string, LocaleInput>` keyed by locale, the primary first |
| `rules(object\|string\|null $model, array $rules, bool $require_all = false)` | `array` |
| `values(?Model $model, ?array $fields = null)` | `array<string, mixed>` |
| `status(?Model $model, ?Closure $url = null)` | `array` of `{locale, translated, primary, url, language}` |
| `shared()` | `{enabled, current, default, direction, locales}` for the front end |

`Locale` has `code`, `locale` (the HTML `lang`), `name`, `native_name`, `direction`, `is_default`, `flag_url`, `isRtl()`, `isThaana()` and `toArray()`. `LocaleInput` has `field`, `name`, `locale` (a `Locale`), `is_primary`, `label(?string $label)` ("Name (ދިވެހި)") and `toArray()`.

# Testing both ways

Test with translatable on, and with it off:

```php
use Javaabu\Translatable\Testing\InteractsWithLanguages;

class IslandTranslationsTest extends TestCase
{
    use InteractsWithLanguages;
    use RefreshDatabase;

    #[Test]
    public function it_saves_the_dhivehi_name(): void
    {
        $this->setUpLanguages(['en', 'dv']); // creates the languages table if needed, adds the languages

        $this->post('/islands', ['name_en' => 'Malé', 'name_dv' => 'މާލެ', 'atoll_id' => $atoll->id]);

        $this->assertEquals('މާލެ', Island::first()->name_dv);
    }

    #[Test]
    public function it_works_without_translations(): void
    {
        $this->withoutTranslatable(function () {
            $this->post('/islands', ['name' => 'Malé', 'atoll_id' => $atoll->id])->assertRedirect();
        });
    }
}
```

To be sure nothing references translatable when it's missing, add a CI leg that removes it before running the tests that don't need it: `composer remove --dev javaabu/translatable --no-update && composer update`.
