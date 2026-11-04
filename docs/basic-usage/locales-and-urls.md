---
title: Locales and URLs
description: The language middleware, translated routes and model URLs
sidebar_position: 7
---

# The `language` middleware

```php
Route::middleware(['web', 'language'])->prefix('{language}')->group(function () {
    Route::get('posts/{post}', [PostsController::class, 'show'])->name('posts.show');
});
```

It picks the locale from, in order: the `{language}` route parameter, a `language` input, the `?lang=` query, the session (not for API requests), then `translatable.default_locale`. It stores it in the session, and sets it as the current translation locale and the app locale. Use `language:0` to leave the app locale alone.

`{language}` is bound to the `Language` model by its `locale`.

# Helpers

| Helper | What |
| --- | --- |
| `translation_locale()` | The current translation locale |
| `translate_route('posts.show', $post, locale: 'dv')` | `route()` with the `language` parameter (default: the current locale) |
| `translate_action([PostsController::class, 'show'], $post)` | `action()` with the `language` parameter |
| `translate_url('posts/1')` | `/{locale}/posts/1` |
| `locale_direction('dv')` | `rtl` |
| `redirect()->translateRoute('posts.show', $post)` | A redirect to a translated route |
| `ma($post, 'title')->translate('dv')` | An attribute wrapper |
| `translate_old('title', $lang_parent)` | Old input, or the parent translation's value |
| `_d('Save')` | `__()` in the fallback locale |

`Translatable::switchUrl('dv')` gives the current page in another language: the current route with `{language}` swapped, or the current URL with `?lang=dv`.

# Model URLs

Translatable models have `url($action = 'show', $locale = null, $portal = null)`, used by the language switcher and the status links:

```php
$post->url();               // posts.show in the current locale
$post->url('edit', 'dv');   // the dv translation's edit page
```

The route name is `{portal}.{models}.{action}` (portal from `request()->portal()`), or `{models}.{action}` without one. For a DB model with no translation in the locale, `edit` and `show` link to `create` with `lang_parent`, or, on `translatable.default_portal`, to `translatable.default_redirect_route`. Override `getModelRouteName()`, `getAdditionalRouteParams()` or `routeKeyForPortal()` in your model to change them.
