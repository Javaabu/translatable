# Translatable: notes for AI coding agents

`javaabu/translatable` makes Eloquent attributes multilingual (JSON on the row, or a row per locale), and ships Blade (javaabu/forms v2) and Inertia React (@javaabu/forms, through javaabu/manifest) components. Its static `Translatable` API is the contract other next-gen packages use for optional translation support.

## Layout

| Path | What |
| --- | --- |
| `src/Translatable.php` | The static contract (`enabled()`, `supports()`, `locales()`, `inputs()`, `rules()`, `values()`, `status()`, `shared()`, …) and v2's instance methods |
| `src/Locale.php`, `src/LocaleInput.php` | Value objects the contract returns; their `toArray()` shapes match `resources/js/types.ts` |
| `src/Traits/IsTranslatable.php` | Shared model behaviour: attribute get/set, suffixes, staging, `fill()` |
| `src/JsonTranslatable`, `src/DbTranslatable` | The two storage traits and their schema macros |
| `src/Languages.php`, `src/LanguageRegistrar.php`, `src/Models/Language.php` | The languages, their cache, and the current locale (scoped) |
| `src/Views/Components` | Blade components; they extend javaabu/forms' base component |
| `resources/views/common` | Blade views, composed only of `<x-forms::…>` components |
| `resources/js` | React components, imported by apps as `@javaabu/translatable` |
| `src/Testing/InteractsWithLanguages.php` | Test helper for apps and packages |
| `lang/{en,dv}/strings.php`, `resources/js/locales` | Every user-facing string; kept identical by `tests/Unit/StringsTest.php` |
| `docs`, `llms.txt`, `resources/boost` | Docs, the assistant index, Laravel Boost guideline and skill |

## Rules

- **The static methods of `Translatable` and the `Locale`/`LocaleInput` array shapes are a public contract** for other packages. Don't change a signature or key within a major; add with defaults. Update `docs/advanced-usage/for-package-authors.md` and the API reference.
- **Both storage types, always.** A model behaviour change needs tests for JSON (`Article`, `Page`) and DB (`Post`) models.
- **Input names are `{field}_{locale}`** for every locale. Setting them stages translations until `save()`; `addTranslation()` saves.
- **Both stacks.** A view change goes in `resources/views/common` and `resources/js`, with the same names, data and strings, and tests in PHPUnit (both forms frameworks via the `frameworks` data provider) and Vitest.
- **No framework classes in Blade views.** Compose javaabu/forms components; use `$layoutClasses()` for the rare wrapper.
- **javaabu/forms, javaabu/manifest and Inertia are optional.** Never reference their classes outside the views/registration code paths, and only by string (`::class` on an unknown class is fine; instantiating isn't).
- **Dates may be immutable.** Apps call `Date::use(CarbonImmutable::class)`, so type-hint `DateTimeInterface` or `CarbonInterface` (never `Carbon\Carbon`) and use the return value of `add…()`/`sub…()`. CI runs the suite with `TRANSLATABLE_IMMUTABLE_DATES=1`; `protected bool $immutable_dates = true` does it for one test class.
- **Never require javaabu/helpers or javaabu/settings** (helpers pins settings v1; see #11). Only `Models/AdminLanguage.php` may use helpers; CI fails if either is in the production tree, and runs the tests without helpers (`--exclude-group helpers`).
- **No hard-coded text.** Add a line to `lang/en`, `lang/dv` and `resources/js/locales/{en,dv}.ts`; list new Dhivehi lines for review in `docs/advanced-usage/translations-and-rtl.md`.
- **RTL:** logical utilities only; never `left`/`right`.
- PHP follows Javaabu's guidelines (`vendor/javaabu/boost-javaabu-guidelines`): Pint, `#[Test]` and `it_…` test names, snake_case variables. Tests use Thaana for user-facing behaviour.
- Every change needs tests, docs (and `llms.txt` for new pages), the Boost skill if usage changes, and a `CHANGELOG.md` line.
- Work happens on `next-gen` until v3 is merged; never commit to `main`.

## Commands

```bash
composer install && npx pnpm@10.28.0 install   # pnpm 10.28 (see package.json packageManager)
composer test            # PHPUnit
composer lint            # Pint (composer format to fix)
pnpm lint && pnpm format:check && pnpm typecheck && pnpm test
```
