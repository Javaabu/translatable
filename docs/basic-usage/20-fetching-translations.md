---
title: Fetching Translations
sidebar_position: 20
---

There are multiple ways to use locale translated attributes within your code. 

## Using application locale

If you use `app()->setLocale('dv');` then translatables will automatically detect this and give you the translated attribute if available.

```php
app()->setLocale('dv');

// ...

// assuming you have added the translation
$post->title // Mee dhivehi title eh
```

## Using language code suffixes

If you need a specific locale, you can use the language code suffix to always get that locale.

```php
$post->title_dv // Mee dhivehi title eh
$post->title_ar // null
```

By default, the language code suffix does not fallback to default locale. To change this behaviour, change the `lang_suffix_should_fallback` to true in the [config](../installation-and-setup.md#publishing-the-config-file) file.
