---
title: React components
description: The Inertia React components, shipped through javaabu/manifest
sidebar_position: 6
---

The React components mirror the [Blade components](./blade-components.md). They're TypeScript in the package's `resources/js`, built by your app's Vite through [javaabu/manifest](https://github.com/Javaabu/manifest), and drawn with [@javaabu/forms](https://github.com/Javaabu/react-forms), so they follow your forms adapter (Onu, shadcn/ui, HTML).

# Setup

1. Install javaabu/manifest and add its Vite plugin ([its docs](https://github.com/Javaabu/manifest)). `@javaabu/translatable` then resolves to `vendor/javaabu/translatable/resources/js`; `php artisan manifest:types` adds the TypeScript paths.
2. Install the npm packages it uses (`php artisan manifest:check` lists them):

   ```bash
   pnpm add github:Javaabu/react-forms#next-gen github:Javaabu/i18n#build-on-git-install
   ```

3. Share the languages with every page:

   ```dotenv
   TRANSLATABLE_VIEWS=inertia
   ```

   With `translatable.views` set to `inertia`, `Translatable::shared()` is shared as the `translatable` prop (`translatable.inertia.prop`).

4. Provide it in your layout:

   ```tsx
   import { usePage } from "@inertiajs/react"
   import { TranslatableProvider, type TranslatableShared } from "@javaabu/translatable"

   export default function Layout({ children }: { children: React.ReactNode }) {
     const { translatable } = usePage<{ translatable: TranslatableShared }>().props

     return <TranslatableProvider value={translatable}>{children}</TranslatableProvider>
   }
   ```

Strings come in English and Dhivehi through [@javaabu/i18n](https://github.com/Javaabu/i18n), in its current locale. Lines your app loads under `translatable::strings.*` win.

# Per-locale fields

```tsx
import { Form, Submit, Text } from "@javaabu/forms"
import { TranslatableField, TranslatableText, TranslatableTextarea } from "@javaabu/translatable"

// $post, plus Translatable::values($post) as `translations`
<Form model={{ ...post, ...translations }}>
  <TranslatableText name="title" label="Title" required primary={post.lang} />
  <TranslatableTextarea name="body" label="Body" rows={6} primary={post.lang} />
  <TranslatableField type="email" name="contact_email" layout="stacked" />
  <Text name="slug" />
  <Submit />
</Form>
```

| Prop | Default | |
| --- | --- | --- |
| `name` | required | |
| `type` (`TranslatableField`) | `text` | `textarea` or any `Input` type |
| `label` | generated | |
| `required` / `requireAll` | `false` | As in Blade |
| `layout` | `tabs` | `tabs` or `stacked` |
| `primary` | the current locale | The model's `lang` |
| `inputs` | from the shared languages | `Translatable::inputs('title', $post)` from the server, to skip `primary` |

Other props go to every input. Without a provider, or with translations off, it renders a plain field.

# Language switcher

```tsx
<LanguageSwitcher />
<LanguageSwitcher url={(locale) => `/${locale.code}/posts/${post.id}`} />
```

The default link is the current URL with `?lang=`, which the `language` middleware reads. For routes with a `{language}` parameter, pass `url` (with Wayfinder or your route helper).

# Translation status, table headings and cells

```php
return Inertia::render('Posts/Index', [
    'posts' => $posts->map(fn (Post $post) => [
        ...$post->toArray(),
        'translation_status' => Translatable::status($post, fn (Locale $locale, bool $translated) => Translatable::translationUrl($post, $locale->code, $translated)),
    ]),
]);
```

```tsx
<TranslationStatus status={post.translation_status} />

<table>
  <thead><tr><TableHeading label="Title" /><TranslationHeadings /></tr></thead>
  <tbody>
    {posts.map((post) => (
      <tr key={post.id}><TableCell value={post.title} /><TranslationCells status={post.translation_status} /></tr>
    ))}
  </tbody>
</table>
```

# Adding a DB translation

```tsx
<TranslationNotice parentId={langParent.id} parentTitle={langParent.title} />
```

# Other exports

| Export | |
| --- | --- |
| `useTranslatable()` | The shared data |
| `localeInputs(field, locales, primary)` | `Translatable::inputs()` on the client |
| `inputName(field, locale)` | `title_dv` |
| `switchUrl(locale)` | The current URL with `?lang=` |
| `useTranslatableStrings()` | The package strings |
| `en`, `dv` | The bundled strings |
| `TranslatableLocale`, `LocaleInput`, `TranslatableShared`, `TranslationStatusItem` | Types matching the PHP arrays |

# Customizing

Override any file at `resources/vendor/translatable/js/…` (javaabu/manifest's override rule), for example `resources/vendor/translatable/js/components/LanguageSwitcher.tsx`.
