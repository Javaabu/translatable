---
title: Configuration
sidebar_position: 2
---

Everything has a default; publish the file only to change one (`php artisan vendor:publish --tag="translatable-config"`).

| Key | Default | What |
| --- | --- | --- |
| `enabled` | `env('TRANSLATABLE_ENABLED', true)` | Turns translations off without uninstalling. Other packages check it through `Translatable::enabled()`. |
| `language_model` | `Javaabu\Translatable\Models\Language` | The languages table model. Extend the default one, or use `Javaabu\Translatable\Models\AdminLanguage` to get javaabu/helpers' `AdminModel` (needs javaabu/helpers). |
| `fields_ignored_for_translation` | `id`, `lang`, `created_at`, `updated_at`, `deleted_at` | Never translated, on every model |
| `lang_suffix_should_fallback` | `false` | Whether `$post->title_dv` falls back to the model's own value when missing |
| `default_locale` | `en` | The language of rows without a `lang`, the middleware's fallback, and the first language in the views. Must be an active language. |
| `thaana_locales` | `['dv']` | Locales whose inputs get the Thaana keyboard |
| `views` | `env('TRANSLATABLE_VIEWS', 'blade')` | `blade` or `inertia`. With `inertia`, the languages are shared with Inertia pages. |
| `inertia.share` | `true` | Share the languages with Inertia pages (when `views` is `inertia`) |
| `inertia.prop` | `translatable` | The prop name |
| `cache.expiration_time` | 24 hours | How long the languages list is cached |
| `cache.key` | `translation.languages.cache` | |
| `cache.driver` | `default` | A cache store name, or `default` for the app's store |
| `default_portal` | `web` | For `$model->url()` (see [Locales and URLs](../basic-usage/locales-and-urls.md#model-urls)) |
| `default_redirect_route` | `home` | Where `$model->url()` links when a DB model has no translation on the default portal |
