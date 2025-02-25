---
title: Introduction
sidebar_position: 1.0
---

# Translatable

[Translatable](https://github.com/Javaabu/translatable) adds multi-lingual to Laravel models. 

This package allows Laravel model attributes to be translated automatically according to the current `app()->getLocale()`.

```php
app()->setLocale('en');
$post->title // This is an English title

app()->setLocale('dv');
$post->title // Mee dhivehi title eh

$post->title_en // This is an English title
$post->title_dv // Mee dhivehi title eh
```

Adding a translation is made easier as well using the language code suffix.

```php
// to add title for dv language
$post->title_dv = "Mee Dhivehi title eh";
```
