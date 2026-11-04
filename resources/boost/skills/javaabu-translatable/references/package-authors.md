# Optional support in a package

Full page: `vendor/javaabu/translatable/docs/advanced-usage/for-package-authors.md`.

```json
{
    "suggest": { "javaabu/translatable": "Translate island names (^3.0)" },
    "require-dev": { "javaabu/translatable": "^3.0" }
}
```

```php
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

- Add nullable `translations` (JSON) and `lang` columns in the package's migration.
- Keep the model class config-overridable; the app (or a subclass the package ships and picks when `Translations::enabled()`) adds `implements JsonTranslatable`, `use IsJsonTranslatable` and `protected array $translatable`. A shipped subclass overrides `getMorphClass()` to the base class.
- Validation: `Translatable::rules($model, $rules)` when supported. Saving: `fill($validated)`.
- Blade: `<x-translatable::text>` when supported, `<x-forms::text>` otherwise.
- React: send `Translatable::inputs($field, $model)` and `Translatable::values($model)` with the page; don't import `@javaabu/translatable`.
- Tests: `InteractsWithLanguages::setUpLanguages()` and `withoutTranslatable()`.
