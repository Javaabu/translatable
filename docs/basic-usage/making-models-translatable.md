---
title: Making models translatable
description: The JSON and DB storage strategies, and the model options
sidebar_position: 2
---

# Choosing a strategy

| | JSON (`IsJsonTranslatable`) | DB (`IsDbTranslatable`) |
| --- | --- | --- |
| Storage | The model's columns hold its primary language; `translations` holds the others | A row per language, linked by `translatable_parent_id` |
| Columns | `$table->jsonTranslatable()`: `translations` (JSON), `lang` | `$table->dbTranslatable()`: `translatable_parent_id`, `lang` |
| Reading a translation | No extra query | A query for the translation's row |
| Each language has its own id, slug, URL | No | Yes |
| Querying and indexing translated values | Through the JSON column | Like any column |
| Package models (stable ids and relations) | Use this | |

Both have the same API, so you can switch later with a data migration.

# JSON

```php
use Illuminate\Database\Eloquent\Model;
use Javaabu\Translatable\Contracts\JsonTranslatable;
use Javaabu\Translatable\JsonTranslatable\IsJsonTranslatable;

class Article extends Model implements JsonTranslatable
{
    use IsJsonTranslatable;

    protected $fillable = ['title', 'body', 'slug', 'author_id'];

    protected array $translatable = ['title', 'body'];
}
```

The trait casts `translations` to an array, unless you cast it yourself.

# DB

```php
use Illuminate\Database\Eloquent\Model;
use Javaabu\Translatable\Contracts\DbTranslatable;
use Javaabu\Translatable\DbTranslatable\IsDbTranslatable;

class Post extends Model implements DbTranslatable
{
    use IsDbTranslatable;

    protected $fillable = ['title', 'body', 'slug', 'author_id'];

    protected array $translatable = ['title', 'body'];

    public function getNonTranslatablePivots(): array
    {
        return ['author_id'];
    }
}
```

A translation row starts as a copy of the original's columns, so every column has a value. Only translatable fields differ between rows.

```php
$post->translations;            // the other rows
$post->defaultTranslation;      // the original, from a translation row
$post->getTranslation('dv');    // the row for a locale, or null
$post->isDefaultTranslation();  // true for the original
Post::rootTranslations()->get(); // originals only
```

# Options

| Method or property | Default | What |
| --- | --- | --- |
| `protected array $translatable` | `[]` | The translatable fields. Or override `getTranslatables()`. |
| `getNonTranslatablePivots()` | `[]` | Foreign keys shared by every translation (DB) |
| `$fields_ignored_for_translation` | `id`, `lang`, and `translations` or `translatable_parent_id` | Never translated; merged with `translatable.fields_ignored_for_translation` |

# The primary locale

A model's `lang` is the language its own columns are in:

- New models get the current locale (`translation_locale()`) when they're first written, unless you set `lang`.
- Rows without a `lang` (columns added to a table that already had data) are read as `translatable.default_locale`, and get it when an attribute is next set on them.

```php
$article->primaryLocale(); // 'en'
$article->locale_direction; // 'ltr'
```
