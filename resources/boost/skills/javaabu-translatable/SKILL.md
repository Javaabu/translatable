---
name: javaabu-translatable
description: Make Eloquent models multilingual with javaabu/translatable in a Laravel app or package, and build their forms in Blade (javaabu/forms) or Inertia React (@javaabu/forms). Use whenever the project depends on javaabu/translatable and the task makes a model translatable, adds languages, reads or writes translations (title_dv), validates or saves a form with per-locale inputs, adds a language switcher or translation status, handles translated routes and the language middleware, or adds optional translatable support to another package.
license: MIT
metadata:
  author: Javaabu
---

# Translations with javaabu/translatable

Translatable attributes are read in the current locale, and each locale is readable and writable with a suffix (`title_dv`). The docs ship with the package: `vendor/javaabu/translatable/llms.txt` lists every page in `vendor/javaabu/translatable/docs/`.

## Languages

Languages are rows of the `languages` table; only active ones can be translated into.

```bash
php artisan translatable:add-languages en dv
```

The default locale (`translatable.default_locale`, `en`) must be one of them.

## Making a model translatable

Prefer JSON storage: one row per record, `translations` JSON for the other languages. Use DB storage (a row per locale) only when each language needs its own id, slug or URL.

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('body');
    $table->string('slug');
    $table->jsonTranslatable();
    $table->timestamps();
});
```

```php
use Illuminate\Database\Eloquent\Model;
use Javaabu\Translatable\Contracts\JsonTranslatable;
use Javaabu\Translatable\JsonTranslatable\IsJsonTranslatable;

class Post extends Model implements JsonTranslatable
{
    use IsJsonTranslatable;

    protected $fillable = ['title', 'body', 'slug'];

    protected array $translatable = ['title', 'body'];
}
```

## Reading and writing

```php
$post->title;                                // current locale, falls back to the model's own value
$post->title_dv;                             // Dhivehi, or null
$post->translate('title', 'dv', fallback: false);
Translatable::values($post);                 // ['title_en' => …, 'title_dv' => …, 'body_en' => …, 'body_dv' => …]

$post->title_dv = 'މަރުޙަބާ';
$post->save();                               // suffixed attributes are written on save

$post->addTranslation('dv', 'title', 'މަރުޙަބާ'); // saves immediately
```

## Forms

One input per locale, named `{field}_{locale}`, the default locale included.

```php
use Javaabu\Translatable\Translatable;

public function rules(): array
{
    return Translatable::rules($this->route('post') ?? Post::class, [
        'title' => ['required', 'string', 'max:255'],
        'body' => ['required', 'string'],
        'slug' => ['required', 'alpha_dash'],
    ]);
    // title_en required, title_dv nullable, body_en, body_dv, slug unchanged
}
```

```php
$post = Post::create($request->validated());
$post->update($request->validated());
```

Blade (javaabu/forms v2):

```blade
<x-forms::form :model="$post ?? null">
    <x-translatable::text name="title" :label="__('Title')" required />
    <x-translatable::textarea name="body" :label="__('Body')" required />
    <x-forms::text name="slug" required />
</x-forms::form>
```

Inertia React (`@javaabu/translatable` through javaabu/manifest, with `TRANSLATABLE_VIEWS=inertia` and a `TranslatableProvider value={usePage().props.translatable}` in the layout):

```tsx
<Form model={{ ...post, ...translations }}> {/* translations = Translatable::values($post) */}
  <TranslatableText name="title" label="Title" required primary={post.lang} />
</Form>
```

## Other components

| Blade | React | |
| --- | --- | --- |
| `<x-translatable::language-switcher />` | `<LanguageSwitcher />` | Links to the current page in each language |
| `<x-translatable::translation-status :model="$post" />` | `<TranslationStatus status={…} />` | A badge per language linking to add/edit (`Translatable::status()`) |
| `<x-translatable::titles />`, `<x-translatable::table-cells :model="$post" />` | `<TranslationHeadings />`, `<TranslationCells status={…} />` | Index table columns |
| `<x-translatable::translation-notice :parent="$lang_parent" />` | `<TranslationNotice … />` | Adding a DB model's translation |

## Routes

```php
Route::middleware(['web', 'language'])->prefix('{language}')->group(function () {
    Route::resource('posts', PostsController::class);
});

translate_route('posts.show', $post, locale: 'dv');
$post->url('edit', 'dv');
```

## Tests

```php
use Javaabu\Translatable\Testing\InteractsWithLanguages;

$this->setUpLanguages(['en', 'dv']);
```

Use Thaana in tests of anything a user sees.

## References

- [Common mistakes](references/common-mistakes.md)
- [Optional support in a package](references/package-authors.md)
