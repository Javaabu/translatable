---
title: Introduction
sidebar_position: 1.0
---

# Introduction

Welcome to the documentation for Translatable, a Laravel package that adds multi-lingual support to your models.

This package allows your Laravel model attributes to be translated automatically according to the current `app()->getLocale()`. This means you can easily create applications that cater to a global audience, without having to worry about the complexities of localization.

Here's a quick example of how it works:

```php
app()->setLocale('en');
$post->title // This is an English title

app()->setLocale('dv');
$post->title // Mee dhivehi title eh

$post->title_en // This is an English title
$post->title_dv // Mee dhivehi title eh
```

As you can see, it's incredibly simple to get and set translations for your model attributes. You can even add new translations on the fly:

```php
// to add title for dv language
$post->title_dv = "Mee Dhivehi title eh";
```

Throughout this documentation, we'll explore all the features of Translatable in detail, from basic usage to advanced customization. We'll also cover how to contribute to the project and where to find help if you need it.