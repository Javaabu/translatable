<?php

namespace Javaabu\Translatable\Contracts;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Javaabu\Translatable\Exceptions\LanguageNotAllowedException;
use Javaabu\Translatable\Models\Language;

interface Translatable
{
    /**
     * Get the translation ignored fields
     */
    public function getFieldsIgnoredForTranslation(): array;

    /**
     * Get the translatable fields
     */
    public function getTranslatables(): array;

    /**
     * Get all attributes that must not be translatable
     */
    public function getNonTranslatables(): array;

    /**
     * Get all pivots that must not be translatable
     */
    public function getNonTranslatablePivots(): array;

    /**
     * Get all pivots and attributes that must not be translatable
     */
    public function getAllNonTranslatables(): array;

    /**
     * Check if is a non-translatable pivot
     */
    public function isNonTranslatablePivot(string $relation): bool;

    /**
     * Translate the given field to given locale.
     * Fall back to default if no translation
     *
     * @return string
     */
    public function translate(string $field, ?string $locale = null, bool $fallback = true): mixed;

    /**
     * Check whether the given field is translatable
     */
    public function isTranslatable(string $field): bool;

    /**
     * Clear the translations for the given locale or all
     */
    public function clearTranslations(?string $locale = null): void;

    /**
     * Check if has any translations or translations for a
     * specific locale
     */
    public function hasTranslation(?string $locale = null): bool;

    /**
     * Check is default translation locale
     */
    public function isDefaultTranslationLocale(string $locale): bool;

    /**
     * Get default translation locale
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
     */
    public function isAllowedTranslationLocale(string $locale): bool;

    /**
     * Add an attribute with a new locale to this object
     *
     * @return $this
     *
     * @throws LanguageNotAllowedException
     */
    public function addTranslation(string $locale, string $field, string $value): static;

    /**
     * Add a new locale to this object
     *
     * @return $this
     */
    public function addTranslations(string $locale, array $fields): static;

    /**
     * Deletes translations for all available locales
     */
    public function deleteTranslations(): void;

    /**
     * Deletes translations for a given locale
     */
    public function deleteTranslation(string $locale): void;

    /**
     * Get the locale direction (ltr or rtl)
     */
    public function localeDirection(): Attribute;

    public function languageSwitcherRouteName(): ?string;

    public function url(string $action = 'show', Language|string|null $locale = null, ?string $portal = null): string;

    public function routeKeyForPortal(?string $portal): string;
}
