<?php

/**
 * Methods that all translatable models should have
 */
namespace Javaabu\Translatable\Old\Abstract;

trait IsTranslatable
{
    protected bool $fallback_translations = false;

    public function __construct()
    {
        $this->fallback_translations = config('translatable.fallback_translations') == 'true';
    }

    /**
     * Check whether to show translation fallbacks
     *
     * @return boolean
     */
    public function shouldFallbackForTranslations(): bool
    {
        return $this->fallback_translations;
    }

    /**
     * Set whether to show translation fallbacks
     */
    public function setShouldFallbackForTranslations(bool $fallback_translations): self
    {
        $this->fallback_translations = $fallback_translations;
        return $this;
    }

    /**
     * Get the translation ignored fields
     *
     * @return array
     */
    public function getFieldsIgnoredForTranslation(): array
    {
        return config('translatable.fields_ignored_for_translation');
    }

    /**
     * Check is default translation locale
     *
     * @param string $locale
     * @return boolean
     */
    public function isDefaultTranslationLocale(string $locale): bool
    {
        return strtolower($this->getDefaultTranslationLocale()) == strtolower($locale);
    }

    /**
     * Get default translation locale
     *
     * @return string
     */
    public function getDefaultTranslationLocale(): string
    {
        return app()->getLocale();
    }

    /**
     * Get allowed translation locales
     *
     * @return array<string>
     */
    public function getAllowedTranslationLocales(): array
    {
        return array_keys(config('translatable.allowed_translation_locales'));
    }

    /**
     * Check if given locale is allowed
     *
     * @param string $locale
     * @return boolean
     */
    public function isAllowedTranslationLocale(string $locale): bool
    {
        return in_array($locale, $this->getAllowedTranslationLocales());
    }

    /**
     * Get all pivots that must not be translatable
     *
     * @return array
     */
    abstract public function getNonTranslatablePivots(): array;

    public function getNonTranslatables() {
        $all_fields = \Schema::getColumnListing($this->getTable());

        $hide = array_merge($this->getTranslatables(), $this->getFieldsIgnoredForTranslation());

        return array_values(array_diff($all_fields, $hide));
    }

    /**
     * Get all pivots and attributes that must not be translatable
     *
     * @return array
     */
    public function getAllNonTranslatables(): array
    {
        return array_merge(
            $this->getNonTranslatablePivots(),
            $this->getNonTranslatables()
        );
    }

    /**
     * Check if is a non translatable pivot
     *
     * @param string $relation
     * @return boolean
     */
    public function isNonTranslatablePivot(string $relation): bool
    {
        return in_array($relation, $this->getNonTranslatablePivots());
    }
}
