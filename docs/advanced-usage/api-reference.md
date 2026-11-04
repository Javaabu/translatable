---
title: API reference
sidebar_position: 5
---

# `Javaabu\Translatable\Translatable`

Static; also through the `Javaabu\Translatable\Facades\Translatable` facade. The contract for other packages, stable within v3. See [For package authors](./for-package-authors.md).

| Method | Returns |
| --- | --- |
| `enabled()` | `bool`: `translatable.enabled` and at least one active language |
| `supports(object\|string\|null $model, ?string $field = null)` | `bool` |
| `isDbTranslatable(object\|string\|null $model)` | `bool` |
| `locales()` | `Collection<int, Locale>`, the default first |
| `locale(?string $code = null)` | `?Locale` (default: the current one) |
| `defaultLocale()`, `currentLocale()` | `string` |
| `primaryLocale(object\|string\|null $model = null)` | `string` |
| `inputName(string $field, string $locale)` | `string` |
| `inputs(string $field, object\|string\|null $model = null)` | `Collection<string, LocaleInput>` |
| `rules(object\|string\|null $model, array $rules, bool $require_all = false)` | `array` |
| `values(?Model $model, ?array $fields = null)` | `array<string, mixed>` |
| `status(?Model $model, ?Closure $url = null)` | `array<int, array{locale, translated, primary, url, language}>` |
| `translationUrl(?Model $model, string $locale, ?bool $translated = null)` | `?string` |
| `switchUrl(string $locale)` | `string` |
| `shared()` | `array{enabled, current, default, direction, locales}` |
| `getAllowedTranslationLocales()`, `isAllowedTranslationLocale($locale)` | v2 instance methods |

# `Locale` and `LocaleInput`

| `Locale` | |
| --- | --- |
| `code`, `locale`, `name`, `native_name`, `direction`, `is_default`, `flag_url` | readonly properties |
| `isRtl()`, `isThaana()` | `bool` |
| `toArray()` | `{code, locale, name, native_name, direction, rtl, thaana, default, flag_url}` |

| `LocaleInput` | |
| --- | --- |
| `field`, `name`, `locale` (a `Locale`), `is_primary` | readonly properties |
| `label(?string $label = null)` | "Title (ދިވެހި)" |
| `toArray()` | `{field, name, locale, direction, thaana, primary, language}` |

# Model methods (both traits)

| Method | |
| --- | --- |
| `getTranslatables()` | The `$translatable` property, unless overridden |
| `isTranslatable(string $field)` | |
| `translate(string $field, ?string $locale = null, bool $fallback = true)` | |
| `hasTranslation(?string $locale = null)` | |
| `addTranslation(string $locale, string $field, mixed $value)` | Saves |
| `addTranslations(string $locale, array $fields)` | Saves |
| `deleteTranslation(string $locale)`, `deleteTranslations()`, `clearTranslations(?string $locale = null)` | |
| `primaryLocale()`, `getDefaultTranslationLocale()`, `isDefaultTranslationLocale(string $locale)` | |
| `getAllowedTranslationLocales()`, `isAllowedTranslationLocale(string $locale)` | |
| `getFillableTranslatables()` | `title_en`, `title_dv`, … |
| `getFieldAndLocale(string $key)` | `['title', 'dv']` for `title_dv` |
| `suffixTranslatables(array $attributes)` | |
| `getNonTranslatables()`, `getAllNonTranslatables()`, `getNonTranslatablePivots()`, `getFieldsIgnoredForTranslation()` | |
| `isFormFieldDisabled(string $field)` | Whether a non-translatable field should be read-only in a translation's form |
| `url(string $action = 'show', $locale = null, ?string $portal = null)` | |
| `locale_direction` attribute | `ltr` / `rtl` |

DB models also have `translations()`, `childTranslations()`, `defaultTranslation()`, `getTranslation(?string $locale)`, `isDefaultTranslation()`, `isRootTranslation()`, `rootTranslations()` scope, `canUpdateTranslatableParent()`, `getPendingTranslations()` and `savePendingTranslations()`.

# `Languages` facade

`all()`, `allExceptCurrent()`, `except($codes)`, `get(?string $code)`, `has(?string $code)`, `default()`, `getDirection(?string $code)`, `isRtl(?string $code)`, `currentLanguageCode()`, `setCurrentLocale(string $locale, bool $set_app_locale = true)`, `defaultLanguageCode()`, `defaultAppLocale()`, `setToTranslationLocale()`, `setToAppLocale()`, `isCurrent($code)`, `isDefault($code = null)`.

# Schema macros

`$table->jsonTranslatable()`, `$table->dropJsonTranslatable()`, `$table->dbTranslatable()`, `$table->dropDbTranslatable()`.

# Command

`php artisan translatable:add-languages {codes?*} {--inactive}`
