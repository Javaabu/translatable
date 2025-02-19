<?php

namespace Javaabu\Translatable\JsonTranslatable;
use Illuminate\Support\Str;

trait IsJsonTranslatable
{
    public array $fields_ignored_for_translation = ['id', 'translations', 'lang'];

    /**
     * Get fields that will be ignored for translation
     *
     * @return array
     */
    public function getFieldsIgnoredForTranslation(): array
    {
        return array_values(array_unique(array_merge(
            $this->fields_ignored_for_translation,
            config('translatable.fields_ignored_for_translation')
        )));
    }

    /**
     * Get non translatable fields without pivots and fields ignored for translation
     *
     * Use <code>getAllNonTranslatables</code> to get non translatables including pivots and fields ignored for translation
     *
     * @return array
     */
    public function getNonTranslatables(): array
    {
        $all_fields = \Schema::getColumnListing($this->getTable());

        $hide = array_merge($this->getTranslatables(), $this->getFieldsIgnoredForTranslation(), $this->getNonTranslatablePivots());

        return array_values(array_diff($all_fields, $hide));
    }

    /**
     * Get all non translatable fields
     *
     * @return array
     */
    public function getAllNonTranslatables(): array
    {
        return array_merge($this->getNonTranslatables(), $this->getFieldsIgnoredForTranslation(), $this->getNonTranslatablePivots());
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
     * Translate a given field to the given locale
     *
     * If fallback is true, fallback to default locale if given locale is unavailable
     *
     * @param string $field
     * @param string|null $locale
     * @param bool $fallback
     * @return mixed
     */
    public function translate(string $field, ?string $locale = null, bool $fallback = true): mixed
    {
        // Use current app locale whenever locale isn't provided
        if (is_null($locale)) {
            $locale = app()->getLocale();
        }

        // If default lang just return the field normally
        if ($this->isDefaultTranslationLocale($locale)) {
            return $this->getAttributeValue($field);
        }

        // If the locale is not allowed then return null
        if (! $this->isAllowedTranslationLocale($locale)) {
            return $fallback ? $this->getAttributeValue($field) : null;
        }

        // If the field is not allowed then return null
        if (! $this->isTranslatable($field)) {
            return $fallback ? $this->getAttributeValue($field) : null;
        }

        $translations = $this->getAttributeValue('translations');

        // Check if translations for that locale exists
        if (! isset($translations[$locale])) {
            return $fallback ? $this->getAttributeValue($field) : null;
        }

        return $translations[$locale][$field];
    }

    /**
     * Check if a given locale is the current default locale
     *
     * @param string $locale
     * @return bool
     */
    public function isDefaultTranslationLocale(string $locale): bool
    {
        return $this->getAttributeValue('lang') == $locale;
    }

    /**
     * Return all the translation locales allowed in the config file
     *
     * @return array
     */
    public function getAllowedTranslationLocales(): array
    {
        return array_keys(config('translatable.allowed_translation_locales'));
    }

    /**
     * Check if a given locale is allowed to translate to
     *
     * @param string $locale
     * @return bool
     */
    public function isAllowedTranslationLocale(string $locale): bool
    {
        return in_array($locale, $this->getAllowedTranslationLocales());
    }

    /**
     * Check if a given field is translatable
     *
     * @param string $field
     * @return bool
     */
    public function isTranslatable(string $field): bool
    {
        return in_array($field, $this->getTranslatables());
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
            return $this->translate($key);
        }

        // check if is a suffixed attribute
        [$field, $locale] = $this->getFieldAndLocale($key);
        if ($locale && $this->isTranslatable($field)) {
            return $this->translate($field, $locale, false);
        }


        // fallback to parent
        return parent::getAttribute($key);
    }

    public function clearTranslations(?string $locale = null): void
    {
        // clear all translations if none is provided
        if (is_null($locale)) {
            $this->translations = null;
            $this->save();
            return;
        }

        $translations = $this->getAttributeValue('translations');

        if (! isset($translations[$locale])) {
            return;
        }

        unset($translations[$locale]);

        $this->translations = $translations;
        $this->save();
    }

    /**
     * Ensures that translations exist for a given locale
     *
     * @param string|null $locale
     * @return bool
     */
    public function hasTranslation(?string $locale = null): bool
    {
        if (is_null($locale)) {
            $locale = app()->getLocale();
        }

        return isset($this->translations[$locale]);
    }
}
