<?php

namespace Javaabu\Translatable\Old\JsonTranslatable;

use Illuminate\Support\Str;
use Javaabu\Translatable\Old\Abstract\IsTranslatable;

trait IsJsonTranslatable {
    use IsTranslatable;

    abstract public function getNonTranslatablePivots(): array;

    public function getNonTranslatables(): array
    {
        $all_fields = \Schema::getColumnListing($this->getTable());

        $hide = array_merge($this->getTranslatables(), $this->getFieldsIgnoredForTranslation());

        return array_values(array_diff($all_fields, $hide));
    }

    public function translate(string $field, ?string $locale = null, bool $fallback = true): mixed
    {
        if (! $locale && $fallback) {
            $locale = app()->getLocale();
        }

        if (! $locale || ! $this->isAllowedTranslationLocale($locale)) {
            return null;
        }

        if ($this->isPrimaryLocale($locale)) {
            // for default locale just use the direct field value
            $value = parent::getAttribute($field);
        } else {
            // for other locales, use the appropriate translation for that locale
            $value = $this->translations[$locale][$field] ?? null;

            if ($value && $this->hasCast($field) && is_string($value)) {
                $value = $this->castAttribute($field, $value);
            }

            // fallback to default if translation missing
            if (empty($value) && $fallback) {
                $value = parent::getAttribute($field);
            }
        }

        return $value;
    }

    public function isPrimaryLocale(string $locale): bool
    {
        return $locale == $this->getAttribute('lang');
    }

    public function isTranslatable(string $field): bool
    {
        return in_array($field, $this->getTranslatables());
    }

    public function clearTranslations(?string $locale = null): void
    {
        $this->translation = null;
    }

    public function hasTranslation(?string $locale = null): bool
    {
        if (is_null($locale)) {
            $locale = app()->getLocale();
        }

        // if the row is already in the correct language
        if ($this->isPrimaryLocale($locale)) {
            return true;
        }

        // if the provided locale is not allowed we can ignore
        if ($this->isAllowedTranslationLocale($locale)) {
            return false;
        }

        // check if translation exists for locale
        $values = $this->translations[$locale];
        return ! empty($values);
    }


    public function getFieldAndLocale(string $key)
    {
        $locale = Str::afterLast($key, '_');

        if (empty($locale) || (! $this->isAllowedTranslationLocale($locale))) {
            return [$key, null];
        }

        $field = Str::beforeLast($locale, '_');
        return [$key, $field];
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function getAttribute($key): mixed
    {
        // Add support for compoships
        if (is_array($key)) { //Check for multi-columns relationship
            return array_map(function ($k) {
                return parent::getAttribute($k);
            }, $key);
        }


        // translate if translatable
        if ($this->isTranslatable($key)) {
            return $this->translate($key, null, $this->shouldFallbackForTranslations());
        }

        // check if is a suffixed attribute
        [$field, $locale] = $this->getFieldAndLocale($key);

        if ($locale && $this->isTranslatable($field)) {
            return $this->translate($field, $locale, false);
        }

        // fallback to parent
        if (($attr = parent::getAttribute($key)) !== null) {
            return $attr;
        }

        return null;
    }
}
