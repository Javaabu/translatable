---
title: Reading and writing translations
description: Translated attributes, suffixes, adding and deleting translations
sidebar_position: 3
---

# Reading

```php
// The current locale (the `language` middleware sets it)
$post->title;

// A locale
$post->title_dv;                              // null when missing
$post->translate('title', 'dv');              // falls back to the model's own value
$post->translate('title', 'dv', fallback: false);

$post->hasTranslation('dv');
$post->toArray(); // translatable fields in the current locale (JSON models)
```

Suffixed attributes don't fall back unless `translatable.lang_suffix_should_fallback` is `true`. Reading in the current locale always falls back to the model's own value.

For a form or an API response, `Translatable::values()` gives every locale, keyed by input name, without falling back:

```php
use Javaabu\Translatable\Translatable;

Translatable::values($post);            // ['title_en' => 'Welcome', 'title_dv' => 'މަރުޙަބާ', 'body_en' => …, 'body_dv' => null]
Translatable::values($post, ['title']); // one field
```

# Writing

## Through attributes (saved with the model)

```php
$post->title_dv = 'މަރުޙަބާ';
$post->save();

$post->fill(['title_en' => 'Welcome', 'title_dv' => 'މަރުޙަބާ'])->save();

Post::create(['title_en' => 'Welcome', 'title_dv' => 'މަރުޙަބާ', 'slug' => 'welcome']);
```

Setting a suffixed attribute stages the translation in memory; it's written when the model is saved, like any attribute. For DB models the translation rows are written right after the original is saved. `fill()`, `create()` and `update()` accept the suffixed names of fillable translatable fields.

Setting a translatable attribute without a suffix writes it in the current locale: when that isn't the model's `lang`, it becomes a translation.

```php
app()->setLocale('dv'); // or the `language` middleware
$post->update(['title' => 'މަރުޙަބާ']); // the dv translation; the English title stays
```

## Straight away

`addTranslation()` and `addTranslations()` save immediately:

```php
$post->addTranslation('dv', 'title', 'މަރުޙަބާ');
$post->addTranslations('dv', ['title' => 'މަރުޙަބާ', 'body' => 'ލިޔުން']);
```

They throw `LanguageNotAllowedException` for a locale that isn't an active language and `FieldNotAllowedException` for a field that isn't translatable. For DB models they return the translation's row.

# Deleting

```php
$post->deleteTranslation('dv');  // throws CannotDeletePrimaryTranslationException for the model's own lang
$post->deleteTranslations();     // every translation, keeps the original
$post->clearTranslations('dv');
$post->clearTranslations();      // DB: force-deletes every translation row
```

For JSON models, `deleteTranslation()` and `deleteTranslations()` change the attribute; call `save()`.
