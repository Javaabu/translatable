<?php

namespace Javaabu\Translatable\Contracts;

use Javaabu\Translatable\Exceptions\LanguageNotAllowedException;

interface Translatable
{
    /**
     * Get the translation ignored fields
     *
     * @return array
     */
    public function getFieldsIgnoredForTranslation(): array;

    /**
     * Get the translatable fields
     *
     * @return array
     */
    public function getTranslatables(): array;

    /**
     * Get all attributes that must not be translatable
     *
     * @return array
     */
    public function getNonTranslatables(): array;

    /**
     * Get all pivots that must not be translatable
     *
     * @return array
     */
    public function getNonTranslatablePivots(): array;

    /**
     * Get all pivots and attributes that must not be translatable
     *
     * @return array
     */
    public function getAllNonTranslatables(): array;

    /**
     * Check if is a non translatable pivot
     *
     * @param string $relation
     * @return boolean
     */
    public function isNonTranslatablePivot(string $relation): bool;

    /**
     * Translate the given field to given locale.
     * Fall back to default if no translation
     *
     * @param $field
     * @param null $locale
     * @param bool $fallback
     * @return string
     */
    public function translate(string $field, ?string $locale = null, bool $fallback = true): mixed;

    /**
     * Check whether the given field is translatable
     *
     * @param string $field
     * @return boolean
     */
    public function isTranslatable(string $field): bool;

    /**
     * Clear the translations for the given locale or all
     *
     * @param null $locale
     */
    public function clearTranslations(?string $locale = null): void;

    /**
     * Check if has any translations or translations for a
     * specific locale
     *
     * @param string $locale
     * @return bool
     */
    public function hasTranslation(?string $locale = null): bool;

    /**
     * Check is default translation locale
     *
     * @param string $locale
     * @return boolean
     */
    public function isDefaultTranslationLocale(string $locale): bool;

    /**
     * Get default translation locale
     *
     * @return string
     */
    public function getDefaultTranslationLocale(): string;

    /**
     * Get allowed translation locales
     *
     * @return array<string>
     */
    public function getAllowedTranslationLocales(): array;

    /**
     * Check if given locale is allowed
     *
     * @param string $locale
     * @return boolean
     */
    public function isAllowedTranslationLocale(string $locale): bool;

    /**
     * Add an attribute with a new locale to this object
     *
     * @param string $locale
     * @param string $field
     * @param string $value
     * @return $this
     * @throws LanguageNotAllowedException
     */
    public function addTranslation(string $locale, string $field, string $value): static;

    /**
     * Add a new locale to this object
     *
     * @param string $locale
     * @param array $fields
     * @return $this
     */
    public function addTranslations(string $locale, array $fields): static;

    /**
     * Deletes translations for all available locales
     *
     * @return void
     */
    public function deleteTranslations(): void;

    /**
     * Deletes translations for a given locale
     *
     * @param string $locale
     * @return void
     */
    public function deleteTranslation(string $locale): void;
}
