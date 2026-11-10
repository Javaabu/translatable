{{--
    javaabu/translatable guidelines for AI code assistants

    Maintained by: Javaabu (https://javaabu.com)
--}}

## javaabu/translatable

- This project makes Eloquent models multilingual with `javaabu/translatable`. A translatable model implements `JsonTranslatable` (or `DbTranslatable`), uses `IsJsonTranslatable` (or `IsDbTranslatable`) and lists its fields in `protected array $translatable`. Forms have one input per locale named `{field}_{locale}` (`title_en`, `title_dv`), validated with `Javaabu\Translatable\Translatable::rules()`. Never add your own translation columns or a `title_dv` column.
- Always activate the `javaabu-translatable` skill when making a model translatable, building a form, table or page for one, adding languages, reading translations, or adding optional translation support to a package.
