<?php

namespace Javaabu\Translatable\JsonTranslatable;
use Illuminate\Support\Arr;
use Javaabu\Translatable\Contracts\Translatable;
use Javaabu\Translatable\Exceptions\FieldNotAllowedException;
use Javaabu\Translatable\Exceptions\LanguageNotAllowedException;
use Javaabu\Translatable\Traits\IsTranslatable;

trait IsJsonTranslatable
{
    use IsTranslatable;

    public static function bootIsJsonTranslatable(): void
    {
        static::creating(function (Translatable $model) {
            $model->lang = $model->lang ?: app()->getLocale();
        });
    }

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

        // If the field is not in the translatable fields list then return null
        if (! $this->isTranslatable($field)) {
            return $fallback ? $this->getAttributeValue($field) : null;
        }

        $translations = $this->getAttributeValue('translations');

        // Check if translations for that locale exists
        if (! isset($translations[$locale])) {
            return $fallback ? $this->getAttributeValue($field) : null;
        }

        return Arr::get($translations[$locale], $field);
    }

    /**
     * Gets default translation locale
     *
     * @return string
     */
    public function getDefaultTranslationLocale(): string
    {
        return $this->getAttributeValue('lang') ?? app()->getLocale();
    }

    /**
     * Clear translations for a given language or for all languages if none is given
     *
     * @param string|null $locale
     * @return void
     */
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

        if ($this->lang === $locale) {
            return true;
        }

        return isset($this->translations[$locale]);
    }

    public function mutateTranslationAttributeValue($field, $value)
    {
        if ($this->isJsonCastable($field) && $value) {
            $value = $this->castAttributeAsJson($field, $value);
        }

        if (! $this->isTranslatable($field)) {
            $this->attributes[$field] = $value;
            return;
        }

        $locale = app()->getLocale();

        if ($this->isDefaultTranslationLocale($locale)) {
            $this->attributes[$field] = $value;
        } else {
            $this->setTranslationAttributeValue($field, $locale, $value);

            // if it's a new model and the default value is not set, set the default
            if ((! $this->exists) && (! parent::getAttribute($value))) {
                $this->attributes[$field] = $value;
            }
        }
    }


    /**
     * Add a new locale to this object
     *
     * @param string $locale
     * @param string $field
     * @param string $value
     * @return $this
     * @throws LanguageNotAllowedException
     * @throws FieldNotAllowedException
     */
    public function addTranslation(string $locale, string $field, $value): static
    {
        if (! $this->isAllowedTranslationLocale($locale)) {
            throw LanguageNotAllowedException::create($locale);
        }

        if (! $this->isTranslatable($field)) {
            throw FieldNotAllowedException::create($field, $locale);
        }

        /** @var array $translations */
        $translations = $this->translations ?? [];

        if ($this->isDefaultTranslationLocale($locale)) {
            $this->setAttribute($field, $value);
            $this->setAttribute('lang', $locale);
            $this->save();
            return $this;
        }

        $translations[$locale] = array_merge(
            array_key_exists($locale, $translations) ? $translations[$locale] : [],
            [$field => $value],
            ['lang' => $locale]
        );
        $this->translations = $translations;

        if (! $this->isDefaultTranslationLocale($locale)) {
            $this->setTranslationAttributeValue($field, $locale, $value);

            // if it's a new model and the default value is not set, set the default
            if ((! $this->exists) && (! parent::getAttribute($field))) {
                parent::setAttribute($field, $value);
            }
        }

        $this->save();

        return $this;
    }
}
