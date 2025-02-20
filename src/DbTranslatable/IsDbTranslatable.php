<?php

namespace Javaabu\Translatable\DbTranslatable;

use Javaabu\Translatable\Abstract\IsTranslatable;

trait IsDbTranslatable
{
    use IsTranslatable;

    public array $fields_ignored_for_translation = ['id', 'translatable_parent_id', 'lang'];

    public function getFieldsIgnoredForTranslation(): array
    {
        return array_values(array_unique(array_merge(
            $this->fields_ignored_for_translation,
            config('translatable.fields_ignored_for_translation')
        )));
    }

    public function translations() {
        return $this->hasMany($this, 'translatable_parent_id');
    }

    public function defaultTranslation() {
        return $this->belongsTo($this, 'translatable_parent_id');
    }

    public function isDefaultTranslation(): bool
    {
        return $this->getAttributeValue('translatable_parent_id') !== null;
    }

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

        if ($this->isDefaultTranslation()) {
            // if there's no parent, this is the main one. get translations using the defined relation
            $translation = $this->translations()->where('lang', $locale)->first();
        } else {
            // otherwise it's already a translation, get all translations including the parent
            $translation = self::query()->where([
                'id' => $this->translatable_parent_id,
                'lang' => $locale,
            ])->orWhere([
                'parent_id' => $this->translatable_parent_id,
                'lang' => $locale,
            ])->first();
        }

        // fallback if the translation doesn't exist
        if (! $translation) {
            return $fallback ? $this->getAttributeValue($field) : null;
        }

        return $translation->getAttributeValue($field);
    }

    public function getDefaultTranslationLocale(): string
    {
        // yes this may not be the correct default translation locale
        // but we do this check to see if we need to fetch another row
        // from the database.
        return $this->getAttributeValue('lang');
    }

    public function clearTranslations(?string $locale = null): void
    {
        if (is_null($locale)) {
            // nuke all except the main one
            $parent_id = $this->isDefaultTranslation() ? $this->id : $this->translatable_parent_id;
            self::query()->where('translatable_parent_id', $parent_id)->forceDelete();
        } else {
            // check the current one lang, if it's correct delete it
            if ($this->lang == $locale) {
                $this->forceDelete();
            } else {
                if ($this->isDefaultTranslation()) {
                    $translation = $this->translations()->where('lang', $locale);
                    $translation->forceDelete();
                } else {
                    $translation = self::query()->where([
                        'translatable_parent_id' => $this->translatable_parent_id,
                        'lang' => $locale,
                    ]);
                    $translation->forceDelete();
                }
            }
        }
    }

    public function hasTranslation(?string $locale = null): bool
    {
        if (is_null($locale)) {
            $locale = app()->getLocale();
        }

        // if the current one is the correct lang no need to fetch from database
        if ($this->lang == $locale) {
            return true;
        }

        if ($this->isDefaultTranslation()) {
            $translation = $this->translations()->where('lang', $locale);
            return $translation->exists();
        } else {
            $translation = self::query()->where([
                'translatable_parent_id' => $this->translatable_parent_id,
                'lang' => $locale,
            ])->orWhere([
                'id' => $this->translatable_parent_id,
                'lang' => $locale,
            ]);
            return $translation->exists();
        }
    }
}
