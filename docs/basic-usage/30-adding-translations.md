---
title: Adding Translations
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

