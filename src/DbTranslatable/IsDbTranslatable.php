<?php

namespace Javaabu\Translatable\DbTranslatable;

use Javaabu\Translatable\Contracts\Translatable;
use Javaabu\Translatable\Exceptions\FieldNotAllowedException;
use Javaabu\Translatable\Exceptions\LanguageNotAllowedException;
use Javaabu\Translatable\Traits\IsTranslatable;

trait IsDbTranslatable
{
    use IsTranslatable;

    public static function bootIsDbTranslatable(): void
    {
        static::creating(function (Translatable $model) {
            if (empty($model->lang)) {
                $model->lang = app()->getLocale();
            }
        });
    }

    public array $fields_ignored_for_translation = ['id', 'translatable_parent_id', 'lang'];

    public function getFieldsIgnoredForTranslation(): array
    {
        return array_values(array_unique(array_merge(
            $this->fields_ignored_for_translation,
            config('translatable.fields_ignored_for_translation')
        )));
    }

    public function translations()
    {
        return $this->hasMany(self::class, 'translatable_parent_id', 'id');
    }

    public function defaultTranslation()
    {
        return $this->belongsTo(self::class, 'translatable_parent_id', 'id');
    }

    public function isDefaultTranslation(): bool
    {
        return $this->getAttributeValue('translatable_parent_id') == null;
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
        if (!$this->isAllowedTranslationLocale($locale))  {
            return $fallback ? $this->getAttributeValue($field) : null;
        }

        // If the field is not in the translatable fields list then return null
        if (!$this->isTranslatable($field)) {
            return $fallback ? $this->getAttributeValue($field) : null;
        }

        // get default translation to check its language first
        $defaultTranslation = $this->isDefaultTranslation() ? $this : $this->defaultTranslation;
        if ($defaultTranslation->lang == $locale) {
            return $defaultTranslation->getAttributeValue($field);
        }
        // attempt to fetch the first translation within translatable rows
        $translation = $defaultTranslation->translations()->where('lang', $locale)->first();

        // fallback if the translation doesn't exist in any of the translated rows
        if (!$translation) {
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
        if (! $locale) {
            // nuke all except the main one
            $parent_id = $this->isDefaultTranslation() ? $this->id : $this->translatable_parent_id;
            self::query()->where('translatable_parent_id', $parent_id)->withTrashed()->forceDelete();
        } else {
            // check the current one lang, if it's correct delete it
            if ($this->lang == $locale) {
                $this->delete();
            } else {
                $defaultTranslation = $this->isDefaultTranslation() ? $this : $this->defaultTranslation;
                if ($defaultTranslation->lang == $locale) {
                    $defaultTranslation->delete();
                }
                $defaultTranslation->translations()->where('lang', $locale)->delete();
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

    /**
     * @throws LanguageNotAllowedException
     * @throws FieldNotAllowedException
     */
    public function addTranslation(string $locale, string $field, string $value): static
    {
        if (! $this->isAllowedTranslationLocale($locale)) {
            throw LanguageNotAllowedException::create($locale);
        }

        if (! $this->isTranslatable($field)) {
            throw FieldNotAllowedException::create($field, $locale);
        }

        $defaultTranslation = $this->isDefaultTranslation() ? $this : $this->defaultTranslation;

        // check if the default translation is already the correct locale
        if ($defaultTranslation->lang == $locale) {
            $defaultTranslation->setAttributeInternal($field, $value);
            $defaultTranslation->save();
            return $defaultTranslation;
        }

        // check if a translated object exists for this locale
        $newTranslation = $defaultTranslation->translations()->where('lang', $locale)->first();

        // if there is none, make a new blank translation of this object
        if (! $newTranslation) {
            $newTranslation = new self();
            // copy all the attributes of the current object to the new translation
            // this ensures no columns are left null
            foreach ($this->getAllAttributes() as $attribute) {
                // TODO: make this primary key rely on some sort of config available per model
                if ($attribute == "id") continue;
                $newTranslation->setAttributeInternal($attribute, $defaultTranslation->getAttributeValue($attribute));
            }
            $newTranslation->setAttributeInternal('translatable_parent_id', $defaultTranslation->id);
            $newTranslation->setAttributeInternal('lang', $locale);
        }

        $newTranslation->setAttributeInternal($field, $value);
        $newTranslation->save();

        return $newTranslation;
    }
}
