# Changelog

All notable changes to `javaabu/translatable` are documented in this file.

## 3.0.0 - Unreleased

- Requires PHP 8.4 and Laravel 12 or 13
- javaabu/helpers is no longer required (it pinned javaabu/settings v1). `Language` keeps `admin_url`, `admin_link_name` and `search()`; apps that need helpers' `AdminModel` set `translatable.language_model` to `Models\AdminLanguage`. CI checks that neither helpers nor settings is in the production dependency tree
- Javaabu codestyle enforced with Pint; CI runs PHP 8.4/8.5 × Laravel 12/13 × lowest/stable, with coverage on Codecov
- **Contract for other packages:** `Translatable::enabled()`, `supports()`, `locales()`, `inputs()`, `rules()`, `values()`, `status()` and `shared()`, with `Locale` and `LocaleInput` value objects. See `docs/advanced-usage/for-package-authors.md`
- `Javaabu\Translatable\Testing\InteractsWithLanguages` for tests in apps and packages
- `php artisan translatable:add-languages en dv` adds languages from built-in presets
- Models can list their fields in `protected array $translatable` instead of overriding `getTranslatables()`
- Setting a suffixed attribute (`$post->title_dv = …`, `fill(['title_dv' => …])`) stages the translation until the model is saved, for both storage types; `fill()` accepts per-locale inputs on DB translatable models too
- `addTranslation()` accepts any value, not only strings
- Rows without a `lang` are read as the default locale, not the current one
- JSON translatable models cast `translations` to an array themselves
- The languages list is cached once per request, an empty list isn't cached, and changing a language clears the cache
- `Languages` and the registrar are scoped, so the current locale doesn't leak between Octane requests
- `languages.local_name` column (the language's own name); `add_local_name_to_languages_table` migration for v2 apps
- New config: `enabled`, `thaana_locales`, `views`, `inertia`
- Works with immutable dates (`Date::use(CarbonImmutable::class)`, the Laravel starter kit default); CI runs the tests with them too
- New DB translation rows get their own `created_at` / `updated_at` instead of copying the parent's
- Fixed: `$model->url()` read `translatable.default_redirect` instead of `default_redirect_route`
- **Blade components on javaabu/forms v2** (Onu and Bootstrap 6): `<x-translatable::text>`, `textarea` and `field` (an input per locale, in tabs or stacked, with direction, language and the Thaana keyboard), `language-switcher`, `translation-status`, `translation-notice` (adding a DB translation), and the rebuilt `titles` / `table-cells`
- `Translatable::translationUrl()` and `Translatable::switchUrl()`
- The `styles` config is gone: the components follow javaabu/forms' framework
- **Inertia React components**, imported as `@javaabu/translatable` through javaabu/manifest: `TranslatableProvider`, `TranslatableField`/`Text`/`Textarea`, `LanguageSwitcher`, `TranslationStatus`, `TranslationHeadings`/`TranslationCells`, `TranslationNotice`, with `en`/`dv` strings through @javaabu/i18n
- With `translatable.views` set to `inertia`, the languages are shared with every Inertia page as the `translatable` prop
- Docs rewritten for people and agents (`docs/`, `llms.txt`), with an upgrade guide from v2; Laravel Boost guideline and skill; `AGENTS.md` for contributors
- Every string in English and Dhivehi, with the PHP and React lines kept identical by a test
