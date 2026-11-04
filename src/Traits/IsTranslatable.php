<?php

namespace Javaabu\Translatable\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Javaabu\Translatable\Exceptions\FieldNotAllowedException;
use Javaabu\Translatable\Exceptions\LanguageNotAllowedException;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Facades\Translatable;
use Javaabu\Translatable\Models\Language;

trait IsTranslatable
{
    private bool $skipTranslation = false;

    /**
     * Get the translatable fields
     *
     * Reads the model's `$translatable` property. Override to compute them.
     *
     * @return array<int, string>
     */
    public function getTranslatables(): array
    {
        return property_exists($this, 'translatable') ? $this->translatable : [];
    }

    /**
     * The locale the model's own columns are written in
     *
     * Existing rows without a `lang` (columns added to a table that already had data)
     * are in the default locale; new models are in the current locale.
     */
    public function primaryLocale(): string
    {
        $lang = $this->getAttributeValue('lang');

        if ($lang) {
            return $lang;
        }

        return $this->exists
            ? Languages::defaultLanguageCode()
            : translation_locale();
    }

    public function getNonTranslatablePivots(): array
    {
        return [];
    }

    /**
     * Get all fields including pivots and fields ignored for translation
     */
    public function getAllAttributes(): array
    {
        return Schema::getColumnListing($this->getTable());
    }

    /**
     * Get non-translatable fields without pivots and fields ignored for translation
     *
     * Use `getAllNonTranslatables` to get non translatables including pivots and fields ignored for translation
     */
    public function getNonTranslatables(): array
    {
        $all_fields = $this->getAllAttributes();

        $hide = array_merge(
            $this->getTranslatables(),
            $this->getFieldsIgnoredForTranslation(),
            $this->getNonTranslatablePivots(),
        );

        return array_values(array_diff($all_fields, $hide));
    }

    /**
     * Get all non-translatable fields, including pivots and fields ignored for translation
     */
    public function getAllNonTranslatables(): array
    {
        return array_merge(
            $this->getNonTranslatables(),
            $this->getFieldsIgnoredForTranslation(),
            $this->getNonTranslatablePivots(),
        );
    }

    /**
     * Check if relation is a non translatable pivot
     */
    public function isNonTranslatablePivot(string $relation): bool
    {
        return in_array($relation, $this->getNonTranslatablePivots());
    }

    /**
     * Check if a given locale is the current default locale
     */
    public function isDefaultTranslationLocale(string $locale): bool
    {
        return $this->getDefaultTranslationLocale() === $locale;
    }

    /**
     * Check if a given locale is allowed to translate to
     */
    public function isAllowedTranslationLocale(string $locale): bool
    {
        return Translatable::isAllowedTranslationLocale($locale);
    }

    /**
     * Return all the translation locales allowed in the config file
     */
    public function getAllowedTranslationLocales(): array
    {
        return Translatable::getAllowedTranslationLocales();
    }

    /**
     * Check if a given field is translatable
     */
    public function isTranslatable(string $field): bool
    {
        //        $languageCodes = Languages::all()->pluck('code')->all();
        //
        //        foreach ($languageCodes as $code) {
        //            if (str_ends_with($field, "_$code")) {
        //                $field = substr($field, 0, -strlen("_$code"));
        //                break;
        //            }
        //        }

        return in_array($field, $this->getTranslatables());
    }

    /**
     * Bulk add translatable fields
     *
     * @return $this
     *
     * @throws LanguageNotAllowedException|FieldNotAllowedException
     */
    public function addTranslations(string $locale, array $fields): static
    {
        if (! $this->isAllowedTranslationLocale($locale)) {
            throw LanguageNotAllowedException::create($locale);
        }

        foreach ($fields as $field => $value) {
            $this->addTranslation($locale, $field, $value);
        }

        return $this;
    }

    /**
     * Get the translatable fillable attributes, suffixed with every allowed locale
     *
     * <code>['title']</code> gives <code>['title_en', 'title_dv']</code>
     *
     * @return array<int, string>
     */
    public function getFillableTranslatables(): array
    {
        $translatable_fillables = $this->getFillable()
            ? array_intersect($this->getTranslatables(), $this->getFillable())
            : $this->getTranslatables();

        $language_codes = $this->getAllowedTranslationLocales();

        $suffixed_fillables = [];

        foreach ($translatable_fillables as $fillable) {
            foreach ($language_codes as $code) {
                $suffixed_fillables[] = $fillable . '_' . $code;
            }
        }

        return $suffixed_fillables;
    }

    protected function fillableTranslatablesFromArray(array $attributes): array
    {
        if ($this->totallyGuarded()) {
            return [];
        }

        return array_intersect_key($attributes, array_flip($this->getFillableTranslatables()));
    }

    /**
     * Fill the model, including suffixed translations (<code>title_dv</code>) of fillable translatable fields
     *
     * Translations are written when the model is saved.
     *
     * @return $this
     *
     * @throws FieldNotAllowedException
     * @throws LanguageNotAllowedException
     */
    public function fill(array $attributes): static
    {
        $fillable_translatables = $this->fillableTranslatablesFromArray($attributes);

        parent::fill(array_diff_key($attributes, $fillable_translatables));

        foreach ($fillable_translatables as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Stage a translation in memory; it is written when the model is saved
     *
     * @return $this
     */
    abstract protected function stageTranslation(string $locale, string $field, mixed $value): static;

    /**
     * Get the field and locale for a given attribute if possible
     *
     * <code>'title_en'</code> would return <code>['title', 'en']</code>
     */
    public function getFieldAndLocale(string $key): array
    {
        $locale = Str::afterLast($key, '_');

        if (empty($locale) || (! $this->isAllowedTranslationLocale($locale))) {
            return [$key, null];
        }

        $field = Str::beforeLast($key, '_');

        return [$field, $locale];
    }

    /**
     * Suffix the translatable fields in the given array
     * with the language codes
     */
    public function suffixTranslatables(array $attributes): array
    {
        $language_codes = Languages::all()->pluck('code')->all();

        $suffixed_attributes = [];

        foreach ($attributes as $attribute) {
            $suffixed_attributes[] = $attribute;

            if ($this->isTranslatable($attribute)) {
                foreach ($language_codes as $code) {
                    $suffixed_attributes[] = $attribute . '_' . $code;
                }
            }
        }

        return $suffixed_attributes;
    }

    /**
     * Helper to check if a form field should be disabled or not
     */
    public function isFormFieldDisabled(string $field): bool
    {
        if ($this->lang === translation_locale()) {
            return false;
        }

        return ! in_array($field, $this->getTranslatables());
    }

    /**
     * Set translation attribute value
     */
    public function setTranslationAttributeValue(string $attribute, Language|string $locale, $translation): void
    {
        if ($locale instanceof Language) {
            $locale = $locale->code;
        }

        $translations = Arr::wrap($this->translations);

        $locale_translations = $translations[$locale] ?? [];
        $locale_translations[$attribute] = $translation;

        $translations[$locale] = $locale_translations;

        $this->translations = $translations;
    }

    public function getAttribute($key): mixed
    {
        // Add support for compoships
        if (is_array($key)) { // Check for multi-columns relationship
            return array_map(function ($k) {
                // recursive call with a string
                return self::getAttribute($k);
            }, $key);
        }

        // Translate using the current app locale if possible
        if ($this->isTranslatable($key)) {
            $translated_value = $this->translate($key, translation_locale());

            // Return translated value if not null, otherwise fallback to original value
            return $translated_value ?? parent::getAttribute($key);
        }

        // check if is a suffixed attribute
        [$field, $locale] = $this->getFieldAndLocale($key);
        if ($locale && Languages::has($locale) && $this->isTranslatable($field)) {
            return $this->translate(
                $field,
                $locale,
                config('translatable.lang_suffix_should_fallback', false)
            );
        }

        // fallback to parent
        return parent::getAttribute($key);
    }

    /**
     * @throws LanguageNotAllowedException
     * @throws FieldNotAllowedException
     */
    public function setAttribute($key, $value): mixed
    {
        // logic for setAttributeInternal
        if ($key === 'skipTranslation' || $this->skipTranslation) {
            return parent::setAttribute($key, $value);
        }

        // set to translation via lang suffix, attr_en
        [$field, $locale] = $this->getFieldAndLocale($key);

        if ($locale && $this->isTranslatable($field)) {
            return $this->stageTranslation($locale, $field, $value);
        }

        // set to translation via app locale
        $lang = $this->primaryLocale();

        if (empty($this->getAttributeValue('lang'))) {
            parent::setAttribute('lang', $lang);
        }

        $locale = translation_locale();

        if ($locale !== $lang && $this->isTranslatable($key) && $this->isAllowedTranslationLocale($locale)) {
            return $this->stageTranslation($locale, $key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * @throws LanguageNotAllowedException
     * @throws FieldNotAllowedException
     */
    protected function setAttributeInternal($key, $value)
    {
        $this->skipTranslation = true;
        $result = $this->setAttribute($key, $value);
        $this->skipTranslation = false;

        return $result;
    }

    /**
     * Get the locale direction (ltr or rtl)
     */
    public function localeDirection(): Attribute
    {
        return Attribute::get(function () {
            return Languages::getDirection($this->primaryLocale());
        });
    }

    public function languageSwitcherRouteName(): ?string
    {
        return null;
    }
}
