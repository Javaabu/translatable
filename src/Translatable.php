<?php

namespace Javaabu\Translatable;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Javaabu\Translatable\Contracts\DbTranslatable;
use Javaabu\Translatable\Contracts\Translatable as TranslatableContract;
use Javaabu\Translatable\DbTranslatable\IsDbTranslatable;
use Javaabu\Translatable\Facades\Languages as LanguagesFacade;
use Javaabu\Translatable\Models\Language;
use Throwable;

/**
 * The entry point for apps and for other packages that support translatable optionally
 *
 * Every static method is part of the contract other packages build on (see
 * docs/advanced-usage/for-package-authors.md), so they keep their signatures within a major.
 */
class Translatable
{
    /**
     * Whether translations are on: the package is enabled in config and at least one language is active
     *
     * Other packages guard with <code>class_exists(Translatable::class) && Translatable::enabled()</code>.
     */
    public static function enabled(): bool
    {
        if (! config('translatable.enabled', true)) {
            return false;
        }

        return LanguagesFacade::all()->isNotEmpty();
    }

    /**
     * Whether the model (an instance or a class name) is translatable, and so is the field when given
     */
    public static function supports(object|string|null $model, ?string $field = null): bool
    {
        if (! $model || ! is_a($model, TranslatableContract::class, true)) {
            return false;
        }

        if (! static::enabled()) {
            return false;
        }

        if ($field === null) {
            return true;
        }

        return static::model($model)->isTranslatable($field);
    }

    /**
     * Whether the model keeps each locale in its own row
     */
    public static function isDbTranslatable(object|string|null $model): bool
    {
        if (! $model) {
            return false;
        }

        return is_a($model, DbTranslatable::class, true)
            || in_array(IsDbTranslatable::class, class_uses_recursive($model), true);
    }

    /**
     * The active languages, the default one first
     *
     * @return Collection<int, Locale>
     */
    public static function locales(): Collection
    {
        $default = static::defaultLocale();

        return LanguagesFacade::all()
            ->map(fn (Language $language) => Locale::fromLanguage($language))
            ->sortBy(fn (Locale $locale) => $locale->code === $default ? 0 : 1)
            ->values();
    }

    /**
     * The given locale, or the current one
     */
    public static function locale(?string $code = null): ?Locale
    {
        $code ??= static::currentLocale();

        return static::locales()->first(fn (Locale $locale) => $locale->code === $code);
    }

    /**
     * The default translation locale (<code>translatable.default_locale</code>)
     */
    public static function defaultLocale(): string
    {
        return LanguagesFacade::defaultLanguageCode();
    }

    /**
     * The locale translatable attributes are read in during this request
     */
    public static function currentLocale(): string
    {
        return LanguagesFacade::currentLanguageCode();
    }

    /**
     * The locale a model's own columns are in: its `lang`, or the current locale for a new model
     */
    public static function primaryLocale(object|string|null $model = null): string
    {
        if ($model instanceof TranslatableContract && method_exists($model, 'primaryLocale')) {
            return $model->primaryLocale();
        }

        return static::currentLocale();
    }

    /**
     * The input name of a field in a locale: <code>title_dv</code>
     */
    public static function inputName(string $field, string $locale): string
    {
        return "{$field}_{$locale}";
    }

    /**
     * One input per active locale for a translatable field, keyed by locale code
     *
     * @return Collection<string, LocaleInput>
     */
    public static function inputs(string $field, object|string|null $model = null): Collection
    {
        $primary = static::primaryLocale($model);

        return static::orderedLocales($primary)
            ->mapWithKeys(fn (Locale $locale) => [
                $locale->code => new LocaleInput(
                    field: $field,
                    name: static::inputName($field, $locale->code),
                    locale: $locale,
                    is_primary: $locale->code === $primary,
                ),
            ]);
    }

    /**
     * Expand validation rules for the model's translatable fields into one rule per locale
     *
     * <code>['title' => ['required', 'string']]</code> becomes <code>title_en</code> with the rules
     * as given for the primary locale, and <code>title_dv</code> with <code>required</code>
     * swapped for <code>nullable</code> for the others. Other fields are returned unchanged.
     *
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    public static function rules(object|string|null $model, array $rules, bool $require_all = false): array
    {
        $expanded = [];

        foreach ($rules as $field => $field_rules) {
            if (! static::supports($model, $field)) {
                $expanded[$field] = $field_rules;

                continue;
            }

            foreach (static::inputs($field, $model) as $input) {
                $expanded[$input->name] = ($input->is_primary || $require_all)
                    ? $field_rules
                    : static::optionalRules($field_rules);
            }
        }

        return $expanded;
    }

    /**
     * The values of the model's translatable fields in every locale, keyed by input name
     *
     * Use it to bind a form (or an Inertia page) to a model: <code>['title_en' => '…', 'title_dv' => '…']</code>.
     * Missing translations are null; nothing falls back to another locale.
     *
     * @param  array<int, string>|null  $fields
     * @return array<string, mixed>
     */
    public static function values(?Model $model, ?array $fields = null): array
    {
        if (! static::supports($model)) {
            return [];
        }

        $values = [];

        foreach ($fields ?? $model->getTranslatables() as $field) {
            foreach (static::locales() as $locale) {
                $values[static::inputName($field, $locale->code)] = $model->translate($field, $locale->code, false);
            }
        }

        return $values;
    }

    /**
     * Whether the model has each locale, for translation status indicators
     *
     * @param  (Closure(Locale, bool): ?string)|null  $url  gives the link for a locale, given whether it's translated
     * @return array<int, array{
     *     locale: string,
     *     translated: bool,
     *     primary: bool,
     *     url: ?string,
     *     language: array<string, mixed>
     * }>
     */
    public static function status(?Model $model, ?Closure $url = null): array
    {
        if (! static::supports($model)) {
            return [];
        }

        $primary = static::primaryLocale($model);

        return static::orderedLocales($primary)
            ->map(function (Locale $locale) use ($model, $primary, $url) {
                $translated = $model->exists && $model->hasTranslation($locale->code);

                return [
                    'locale' => $locale->code,
                    'translated' => $translated,
                    'primary' => $locale->code === $primary,
                    'url' => $url ? $url($locale, $translated) : null,
                    'language' => $locale->toArray(),
                ];
            })
            ->all();
    }

    /**
     * The link to add or edit the model's translation in a locale, through the model's url() (v2's URL generators)
     *
     * Translated: the edit page in that locale. Not translated: the create page with
     * `lang_parent` for DB translatable models, the edit page for JSON ones. Null when the
     * model has no url() or the route doesn't exist.
     */
    public static function translationUrl(?Model $model, string $locale, ?bool $translated = null): ?string
    {
        if (! $model || ! method_exists($model, 'url')) {
            return null;
        }

        $translated ??= $model->exists && $model->hasTranslation($locale);

        $action = (! $translated && static::isDbTranslatable($model)) ? 'create' : 'edit';

        try {
            $url = $model->url($action, $locale);
        } catch (Throwable) {
            return null;
        }

        if ($action === 'create' && ! str_contains($url, 'lang_parent=')) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query(['lang_parent' => $model->getKey()]);
        }

        return $url;
    }

    /**
     * The current page in another locale: the current route with its {language} parameter
     * swapped, or the current URL with ?lang= (which the `language` middleware reads)
     */
    public static function switchUrl(string $locale): string
    {
        $request = request();
        $route = $request->route();

        if ($route instanceof Route && $route->getName() && $route->hasParameter('language')) {
            $url = route($route->getName(), [...$route->parameters(), 'language' => $locale]);
            $query = Arr::except($request->query(), ['lang']);

            return $query ? $url . '?' . Arr::query($query) : $url;
        }

        return $request->fullUrlWithQuery(['lang' => $locale]);
    }

    /**
     * Data for the front end (Inertia shared props, or a script tag)
     *
     * @return array{
     *     enabled: bool,
     *     current: string,
     *     default: string,
     *     direction: string,
     *     locales: array<int, array<string, mixed>>
     * }
     */
    public static function shared(): array
    {
        $current = static::currentLocale();

        return [
            'enabled' => static::enabled(),
            'current' => $current,
            'default' => static::defaultLocale(),
            'direction' => static::locale($current)?->direction ?? 'ltr',
            'locales' => static::locales()->map->toArray()->all(),
        ];
    }

    /**
     * Return all the translation locales allowed
     *
     * @return array<int, string>
     */
    public function getAllowedTranslationLocales(): array
    {
        return LanguagesFacade::all()->map(function ($lang) {
            return $lang->code;
        })->values()->toArray();
    }

    /**
     * Check if a given locale is allowed to translate to
     */
    public function isAllowedTranslationLocale(Language|string $locale): bool
    {
        if ($locale instanceof Language) {
            $locale = $locale->code;
        }

        return in_array($locale, $this->getAllowedTranslationLocales());
    }

    /**
     * The locales with the primary one first
     *
     * @return Collection<int, Locale>
     */
    protected static function orderedLocales(string $primary): Collection
    {
        return static::locales()
            ->sortBy(fn (Locale $locale) => $locale->code === $primary ? 0 : 1)
            ->values();
    }

    protected static function model(object|string $model): TranslatableContract
    {
        return is_string($model) ? new $model : $model;
    }

    protected static function optionalRules(mixed $rules): mixed
    {
        $is_string = is_string($rules);

        $rules = match (true) {
            $is_string => explode('|', $rules),
            is_array($rules) => $rules,
            default => [$rules],
        };

        $rules = array_values(array_filter($rules, fn ($rule) => $rule !== 'required'));

        if (! in_array('nullable', $rules, true)) {
            array_unshift($rules, 'nullable');
        }

        return $is_string ? implode('|', $rules) : $rules;
    }
}
