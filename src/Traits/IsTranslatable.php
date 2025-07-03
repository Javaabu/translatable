<?php

namespace Javaabu\Translatable\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Javaabu\Translatable\Exceptions\FieldNotAllowedException;
use Javaabu\Translatable\Exceptions\LanguageNotAllowedException;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Facades\Translatable;
use Javaabu\Translatable\LanguageRegistrar;
use Javaabu\Translatable\Models\Language;

trait IsTranslatable
{
    private bool $skipTranslation = false;

    public function getNonTranslatablePivots(): array
    {
        return [];
    }

    /**
     * Get all fields including pivots and fields ignored for translation
     *
     * @return array
     */
    public function getAllAttributes(): array
    {
        return \Schema::getColumnListing($this->getTable());
    }

    /**
     * Get non-translatable fields without pivots and fields ignored for translation
     *
     * Use `getAllNonTranslatables` to get non translatables including pivots and fields ignored for translation
     *
     * @return array
     */
    public function getNonTranslatables(): array
    {
        $all_fields = $this->getAllAttributes();


        $hide = array_merge(
            $this->getTranslatables(),
            $this->getFieldsIgnoredForTranslation(),
            $this->getNonTranslatablePivots()
        );

        return array_values(array_diff($all_fields, $hide));
    }

    /**
     * Get all non-translatable fields, including pivots and fields ignored for translation
     *
     * @return array
     */
    public function getAllNonTranslatables(): array
    {
        return array_merge(
            $this->getNonTranslatables(),
            $this->getFieldsIgnoredForTranslation(),
            $this->getNonTranslatablePivots()
        );
    }

    /**
     * Check if relation is a non translatable pivot
     *
     * @param string $relation
     * @return bool
     */
    public function isNonTranslatablePivot(string $relation): bool
    {
        return in_array($relation, $this->getNonTranslatablePivots());
    }

    /**
     * Check if a given locale is the current default locale
     *
     * @param string $locale
     * @return bool
     */
    public function isDefaultTranslationLocale(string $locale): bool
    {
        return $this->getDefaultTranslationLocale() === $locale;
    }

    /**
     * Check if a given locale is allowed to translate to
     *
     * @param string $locale
     * @return bool
     */
    public function isAllowedTranslationLocale(string $locale): bool
    {
        return Translatable::isAllowedTranslationLocale($locale);
    }

    /**
     * Return all the translation locales allowed in the config file
     *
     * @return array
     */
    public function getAllowedTranslationLocales(): array
    {
        return Translatable::getAllowedTranslationLocales();
    }

    /**
     * Check if a given field is translatable
     *
     * @param string $field
     * @return bool
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
     * @param string $locale
     * @param array $fields
     * @return $this
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
     * Get the field and locale for a given attribute if possible
     *
     * <code>'title_en'</code> would return <code>['title', 'en']</code>
     *
     * @param string $key
     * @return array
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
     *
     * @param array $attributes
     * @return array
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
     *
     * @param  string  $field
     * @return bool
     */
    public function isFormFieldDisabled(string $field): bool
    {
        if ($this->lang === app()->getLocale()) {
            return false;
        }

        return ! in_array($field, $this->getTranslatables());
    }

    /**
     * Set translation attribute value
     *
     * @param  string           $attribute
     * @param  Language|string  $locale
     * @param                   $translation
     */
    public function setTranslationAttributeValue(string $attribute, Language|string $locale,  $translation): void
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
        if (is_array($key)) { //Check for multi-columns relationship
            return array_map(function ($k) {
                // recursive call with a string
                return self::getAttribute($k);
            }, $key);
        }

        // translate using current app locale if possible
        if ($this->isTranslatable($key)) {
            return $this->translate($key, app()->currentLocale());
        }

        // check if is a suffixed attribute
        [$field, $locale] = $this->getFieldAndLocale($key);
        if ($locale && Languages::has($locale) && $this->isTranslatable($field)) {
            return $this->translate($field, $locale, config('translatable.lang_suffix_should_fallback', false));
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
            return $this->addTranslation($locale, $field, $value);
        }

        // set to translation via app locale
        $lang = $this->getAttribute('lang');
        $locale = app()->getLocale();
        if (empty($lang)) {
            parent::setAttribute("lang", $locale);
            $lang = $locale;
        }
        if ($locale !== $lang && $this->isTranslatable($field)) {
            return $this->addTranslation($locale, $field, $value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * @throws LanguageNotAllowedException
     * @throws FieldNotAllowedException
     */
    protected function setAttributeInternal($key, $value) {
        $this->skipTranslation = true;
        $result = $this->setAttribute($key, $value);
        $this->skipTranslation = false;
        return $result;
    }
}
