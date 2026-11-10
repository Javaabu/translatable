<?php

namespace Javaabu\Translatable\JsonTranslatable;

use Illuminate\Support\Arr;
use Javaabu\Translatable\Contracts\Translatable;
use Javaabu\Translatable\Exceptions\CannotDeletePrimaryTranslationException;
use Javaabu\Translatable\Exceptions\FieldNotAllowedException;
use Javaabu\Translatable\Exceptions\LanguageNotAllowedException;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Traits\IsTranslatable;
use Javaabu\Translatable\Traits\UrlGenerators\JsonTranslatedUrlGenerator;

trait IsJsonTranslatable
{
    use IsTranslatable;
    use JsonTranslatedUrlGenerator;

    public static function bootIsJsonTranslatable(): void
    {
        static::creating(function (Translatable $model) {
            $model->lang = $model->lang ?: translation_locale();
        });
    }

    public array $fields_ignored_for_translation = ['id', 'translations', 'lang'];

    public function initializeIsJsonTranslatable(): void
    {
        if (! $this->hasCast('translations')) {
            $this->mergeCasts(['translations' => 'array']);
        }
    }

    /**
     * Get fields that will be ignored for translation
     */
    public function getFieldsIgnoredForTranslation(): array
    {
        return array_values(array_unique(array_merge(
            $this->fields_ignored_for_translation,
            config('translatable.fields_ignored_for_translation'),
        )));
    }

    /**
     * Translate a given field to the given locale
     *
     * If fallback is true, fallback to default locale if given locale is unavailable
     */
    public function translate(string $field, ?string $locale = null, bool $fallback = true): mixed
    {
        // Use current app locale whenever locale isn't provided
        if ($locale === null) {
            $locale = translation_locale();
        }

        // If default lang just return the field normally
        if ($this->isDefaultTranslationLocale($locale)) {
            return $this->getAttributeValue($field);
        }

        // If the locale is not allowed then return null
        if (! $this->isAllowedTranslationLocale($locale)) {
            return $fallback ? $this->getAttributeValue($field) : null;
        }

        // If the field is not in the translatable fields list then return null
        if (! $this->isTranslatable($field)) {
            return $fallback ? $this->getAttributeValue($field) : null;
        }

        $translations = $this->getAttributeValue('translations');

        if (isset($translations[$locale]) && array_key_exists($field, $translations[$locale])) {
            $value = $translations[$locale][$field];

            if (! $value && $fallback) {
                // If the value is empty, return the default value if fallback is true
                return $this->getAttributeValue($field);
            }

            return $value;
        }

        // Check if translations for that locale exists
        if (! isset($translations[$locale])) {
            return $fallback ? $this->getAttributeValue($field) : null;
        }

        return Arr::get($translations[$locale], $field);
    }

    /**
     * Gets default translation locale
     */
    public function getDefaultTranslationLocale(): string
    {
        return $this->primaryLocale();
    }

    /**
     * Clear translations for a given language or for all languages if none is given
     */
    public function clearTranslations(?string $locale = null): void
    {
        // clear all translations if none is provided
        if ($locale === null) {
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
     */
    public function hasTranslation(?string $locale = null): bool
    {
        if ($locale === null) {
            $locale = translation_locale();
        }

        if ($this->lang === $locale) {
            return true;
        }

        return isset($this->translations[$locale]);
    }

    /**
     * Add a translation and save the model
     *
     * @return $this
     *
     * @throws LanguageNotAllowedException
     * @throws FieldNotAllowedException
     */
    public function addTranslation(string $locale, string $field, mixed $value): static
    {
        $this->stageTranslation($locale, $field, $value);

        $this->save();

        return $this;
    }

    /**
     * Put a translation in the translations column without saving
     *
     * @return $this
     *
     * @throws LanguageNotAllowedException
     * @throws FieldNotAllowedException
     */
    protected function stageTranslation(string $locale, string $field, mixed $value): static
    {
        if (! $this->isAllowedTranslationLocale($locale)) {
            throw LanguageNotAllowedException::create($locale);
        }

        if (! $this->isTranslatable($field)) {
            throw FieldNotAllowedException::create($field, $locale);
        }

        if ($this->isDefaultTranslationLocale($locale)) {
            $this->setAttributeInternal($field, $value);
            $this->setAttributeInternal('lang', $locale);

            return $this;
        }

        if (empty($this->getAttributeValue('lang'))) {
            $this->setAttributeInternal('lang', $this->primaryLocale());
        }

        $translations = Arr::wrap($this->getAttributeValue('translations'));

        $translations[$locale] = array_merge(
            $translations[$locale] ?? [],
            [$field => $value],
            ['lang' => $locale],
        );

        $this->setAttributeInternal('translations', $translations);

        // A new model needs its own columns filled, so a translation fills them until the primary value is set
        if ((! $this->exists) && (! parent::getAttribute($field))) {
            $this->setAttributeInternal($field, $value);
        }

        return $this;
    }

    /**
     * @throws CannotDeletePrimaryTranslationException
     */
    public function deleteTranslation(string $locale): void
    {
        if ($locale === $this->getAttributeValue('lang')) {
            throw CannotDeletePrimaryTranslationException::create($locale);
        }

        if (! array_key_exists($locale, Arr::wrap($this->translations))) {
            return;
        }

        $this->skipTranslation = true;

        $translations = $this->translations ?? [];
        if (! $this->isDefaultTranslationLocale($locale)) {
            unset($translations[$locale]);
        } else {
            throw CannotDeletePrimaryTranslationException::create($locale);
        }
        $this->translations = $translations;

        $this->skipTranslation = false;
    }

    public function deleteTranslations(): void
    {
        $this->translations = [];
    }

    /**
     * Get the value of an attribute using its mutator for array conversion.
     *
     * @param  string  $key
     */
    protected function mutateAttributeForArray($key, $value)
    {
        // check if is a suffixed attribute
        [$field, $locale] = $this->getFieldAndLocale($key);

        if ($locale && $this->isTranslatable($field)) {
            return $this->{$key};
        }

        return parent::mutateAttributeForArray($key, $value);
    }

    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        return $this->addTranslatableAttributesToArray($attributes);
    }

    /**
     * Add the translatable attributes to the attributes array.
     */
    protected function addTranslatableAttributesToArray(array $attributes): array
    {
        foreach ($this->getTranslatables() as $key) {
            if (! isset($attributes[$key])) {
                continue;
            }

            $attributes[$key] = $this->translate($key);
        }

        return $attributes;
    }
}
