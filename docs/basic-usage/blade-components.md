---
title: Blade components
description: Per-locale fields, language switcher, translation status, table cells and the add-translation notice
sidebar_position: 5
---

The components are built on [javaabu/forms](https://github.com/Javaabu/forms) v2, so install it (`composer require javaabu/forms:^2.0`) and they follow its framework (`forms.framework`: `onu` or `bootstrap-6`). Every one has a [React twin](./react-components.md).

They need no npm packages from translatable: its React dependencies (`@javaabu/forms`, `@javaabu/i18n`, React) are only for Inertia apps, so `php artisan manifest:check` doesn't ask a Blade app for them.

# Per-locale fields

```blade
<x-forms::form :model="$post">
    <x-translatable::text name="title" :label="__('Title')" required />
    <x-translatable::textarea name="body" :label="__('Body')" :rows="6" />
    <x-translatable::field type="email" name="contact_email" layout="stacked" />
</x-forms::form>
```

Renders one javaabu/forms input per active language (`title_en`, `title_dv`), in tabs titled with each language's own name:

- The model's primary locale comes first. `required` only applies to it (add `require-all` for every language).
- Values come from the bound model (`$post->title_dv`), old input and errors as usual. The first tab with an error opens.
- Each input gets its language's `dir` and `lang`; Dhivehi inputs get the Thaana keyboard (`translatable.thaana_locales`).
- Labels are "Title (ދިވެހި)".
- `layout="stacked"` shows the inputs one after another instead of in tabs.
- Other attributes (`rows`, `placeholder`, `help`, …) are passed to every input; `class` and `id` go on the wrapper.
- When the model or field isn't translatable, or translations are off, it renders a plain `<x-forms::{type} name="title">`.

| Attribute | Default | |
| --- | --- | --- |
| `name` | required | The field |
| `type` (`field` only) | `text` | Any javaabu/forms input: `text`, `textarea`, `email`, `wysiwyg`, … |
| `label` | generated | |
| `model` | the form's model | |
| `required` | `false` | Required in the primary locale |
| `require-all` | `false` | Required in every locale |
| `layout` | `tabs` | `tabs` or `stacked` |
| `framework` | `forms.framework` | |

# Language switcher

```blade
<x-translatable::language-switcher />
<x-translatable::language-switcher :model="$post" action="show" />
```

A link per active language, titled with its own name, the current one marked. It links to the current page in that language ([`Translatable::switchUrl()`](./locales-and-urls.md#helpers)), or to the model's page with `:model`. Hidden with fewer than two languages.

# Translation status

```blade
<x-translatable::translation-status :model="$post" />
<x-translatable::translation-status :model="$post" :links="false" />
```

A badge per language: translated or not, linking to edit the translation or add it (the create page with `lang_parent` for DB models). Links come from `$model->url()`; without matching routes the badges show without links.

# Table headings and cells

```blade
<x-forms::table>
    <x-slot:headers>
        <x-forms::table.heading :label="__('Title')" />
        <x-translatable::titles />
    </x-slot:headers>

    @foreach($posts as $post)
        <x-forms::table.row>
            <x-forms::table.cell name="title" :model="$post" />
            <x-translatable::table-cells :model="$post" />
        </x-forms::table.row>
    @endforeach
</x-forms::table>
```

A column per language except the current one, with an edit (pencil) or add (plus) link per row.

# Adding a DB translation

```blade
<x-translatable::translation-notice :parent="$lang_parent" />
```

"You are adding the ދިވެހި translation of “Welcome”.", with hidden `lang` and `lang_parent` inputs. Renders nothing without a parent. `locale` (default: the current one) and `title-attribute` (default: `title`, then `name`) are optional. See [Forms](./forms-and-validation.md#adding-a-translation-of-a-db-model).

# Customizing

```bash
php artisan vendor:publish --tag="translatable-views"
```

Copies the views to `resources/views/vendor/translatable/common`. Add `resources/views/vendor/translatable/{framework}/{view}.blade.php` to change one framework only.
