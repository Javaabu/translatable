---
title: Forms and validation
description: Per-locale inputs, validation rules, saving, and binding values
sidebar_position: 4
---

A translatable field has **one input per active language**, named `{field}_{locale}`, the default language included: `title_en`, `title_dv`. Validation, old input, model binding and `fill()` all use these names.

# Validation

`Translatable::rules()` expands your rules for the model's translatable fields. The model's primary locale keeps the rules you wrote; the others get `nullable` in place of `required`:

```php
use Javaabu\Translatable\Translatable;

class PostRequest extends FormRequest
{
    public function rules(): array
    {
        return Translatable::rules($this->route('post') ?? Post::class, [
            'title' => ['required', 'string', 'max:255'],
            'body'  => ['required', 'string'],
            'slug'  => ['required', 'alpha_dash'],
        ]);
    }
}
// title_en: required|string|max:255, title_dv: nullable|string|max:255, body_en, body_dv, slug
```

- Pass `require_all: true` to require every language.
- Rules can be arrays, `|` strings or rule objects.
- Fields that aren't translatable, and every field when translations are off, pass through unchanged.
- The primary locale is the model's `lang`; for a class name (a create form), the current locale.

# Saving

```php
public function store(PostRequest $request)
{
    $post = Post::create($request->validated());

    return redirect()->to($post->url('show'));
}

public function update(PostRequest $request, Post $post)
{
    $post->update($request->validated());

    return redirect()->to($post->url('show'));
}
```

# The form

Blade:

```blade
<x-forms::form :model="$post ?? null" :action="...">
    <x-translatable::text name="title" :label="__('Title')" required />
    <x-translatable::textarea name="body" :label="__('Body')" required />
    <x-forms::text name="slug" required />
    <x-forms::submit />
</x-forms::form>
```

React (Inertia), with `Translatable::values($post)` merged into the model you pass:

```tsx
<Form model={{ ...post, ...translations }}>
  <TranslatableText name="title" label="Title" required primary={post?.lang} />
  <TranslatableTextarea name="body" label="Body" required primary={post?.lang} />
  <Text name="slug" required />
  <Submit />
</Form>
```

See [Blade components](./blade-components.md) and [React components](./react-components.md).

# Adding a translation of a DB model

DB models have a row per language, so a translation is created from a form of its own, in the target locale, with the original's id as `lang_parent`. The [status badges and table cells](./blade-components.md#translation-status) link there.

```php
public function create(Request $request)
{
    $lang_parent = $request->query('lang_parent') ? Post::findOrFail($request->query('lang_parent')) : null;

    return view('posts.create', compact('lang_parent'));
}

// The translation's form has the plain fields, in the locale being added
public function storeTranslation(Request $request)
{
    $post = new Post($request->validate([
        'title' => ['required', 'string', 'max:255'],
        'body'  => ['required', 'string'],
        'slug'  => ['required', 'alpha_dash'],
    ]));

    if ($parent = Post::find($request->input('lang_parent'))) {
        $post->translatable_parent_id = $parent->id;
        $post->lang = $request->input('lang');
    }

    $post->save();

    return redirect()->to($post->url('show'));
}
```

```blade
<x-forms::form :model="$lang_parent">
    <x-translatable::translation-notice :parent="$lang_parent" />
    <x-forms::text name="title" required />
    ...
</x-forms::form>
```

`translate_old('title', $lang_parent)` gives the parent's value as the starting value.
