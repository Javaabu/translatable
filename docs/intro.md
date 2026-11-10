---
title: Introduction
sidebar_position: 1.0
---

# Introduction

Translatable makes Eloquent model attributes multilingual. Attributes are read in the current locale automatically, each locale can be read and written directly (`title_dv`), and forms get one input per language.

```php
app()->setLocale('en');
$post->title;    // Welcome

app()->setLocale('dv');
$post->title;    // މަރުޙަބާ

$post->title_en; // Welcome
$post->title_dv; // މަރުޙަބާ
```

It has two ways to store translations:

- **JSON** (`IsJsonTranslatable`): the model's own columns hold its primary language, and a `translations` JSON column holds the others. One row per record.
- **DB** (`IsDbTranslatable`): every language is its own row, linked to the original by `translatable_parent_id`.

And it ships the views to edit them, for both of Javaabu's stacks:

- **Blade** components on [javaabu/forms](https://github.com/Javaabu/forms) v2: per-language tabs for a field, a language switcher, translation status badges and the add-translation flow.
- **Inertia React** components on [@javaabu/forms](https://github.com/Javaabu/react-forms), shipped to your app through [javaabu/manifest](https://github.com/Javaabu/manifest).

Other Javaabu packages (permissions, settings, locations, menus, cms, …) support translatable optionally, through the [contract for package authors](./advanced-usage/for-package-authors.md).

Dhivehi is a first-class language: Thaana keyboard on Dhivehi inputs, right-to-left layouts, and every string of the package in English and Dhivehi.
