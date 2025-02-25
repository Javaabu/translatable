---
title: Adding Translations
description: How to add translations for your Translatable models
sidebar_position: 30
---

Translations can be added using the `addTranslation` / `addTranslations` methods available on models with Translatables.

```php
// add translation for one attribute
$post->addTranslation('dv', 'title', 'Mee dhivehi title eh');
$post->addTranslation('dv', 'body', 'Mee dhivehi liyumeh');

// add translations for multiple attributes at once
$post->addTranslations('dv', [
    'title' => 'Mee dhivehi title eh',
    'body' => 'Mee dhivehi liyumeh'
]);

$post->title_dv // Mee dhivehi title eh
$post->body_dv // Mee dhivehi liyumeh
```

---

Translations can also be added via the setters.

```php
$post->title_dv = "Mee dhivehi title eh";
$post->body_dv = "Mee dhivehi liyumeh";

app()->setLocale('dv');
$post->title // Mee dhivehi title eh
$post->body // Mee dhivehi liyumeh
```

> If adding translations give an error, make sure the locale is allowed in `allowed_translation_locales` in `config/translatable.php`. Check out [Installation and Setup > Publishing the config file](../installation-and-setup.md#publishing-the-config-file) for information on how to setup your config file.
