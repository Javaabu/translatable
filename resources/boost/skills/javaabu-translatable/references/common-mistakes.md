# Common mistakes

- **Adding `title_dv` columns.** Translations live in `translations` (JSON) or in other rows (DB). `title_dv` is only an attribute name.
- **Naming inputs `title[dv]` or `dv[title]`.** Inputs are `title_en`, `title_dv`. Use `Translatable::rules()` and `fill()` with them.
- **Expecting `$post->title_dv = …` to save.** It's written on `save()`. Only `addTranslation()`/`addTranslations()` save immediately.
- **Requiring every locale by hand.** `Translatable::rules()` requires the primary locale only; pass `require_all: true` to require all.
- **Forgetting the default language.** If `translatable.default_locale` isn't an active row of `languages`, nothing can be translated.
- **Reading `$post->title` to get one locale.** It follows the current locale; use `$post->title_dv` or `translate('title', 'dv', fallback: false)`.
- **Using DB storage for package models.** It changes ids per language; use JSON.
- **Hard-coding `dir="rtl"`.** The components set each input's direction and the Thaana keyboard from the language.
- **Calling `Translatable::` from a package without `class_exists()`.** It's optional for other packages.
