<?php

namespace Javaabu\Translatable\DbTranslatable;

use Illuminate\Database\Eloquent\Builder;
use Javaabu\Translatable\Contracts\Translatable;
use Javaabu\Translatable\Exceptions\CannotDeletePrimaryTranslationException;
use Javaabu\Translatable\Exceptions\FieldNotAllowedException;
use Javaabu\Translatable\Exceptions\LanguageNotAllowedException;
use Javaabu\Translatable\Traits\IsTranslatable;
use Javaabu\Translatable\Traits\UrlGenerators\DbTranslatedUrlGenerator;

trait IsDbTranslatable
{
    use DbTranslatedUrlGenerator;
    use IsTranslatable;

    /**
     * Translations set through attributes, written to their rows after the model is saved
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $pending_translations = [];

    public static function bootIsDbTranslatable(): void
    {
        static::creating(function (Translatable $model) {
            // If the model is being created and the developer has not set the `lang`
            // attributes, we will set it to the current application locale.
            if (empty($model->lang)) {
                $model->lang = translation_locale();
            }
        });

        static::saved(function (Translatable $model) {
            $model->savePendingTranslations();
        });
    }

    /**
     * Write the translations staged through attributes (<code>$post->title_dv = …</code>)
     */
    public function savePendingTranslations(): void
    {
        $pending = $this->pending_translations;

        $this->pending_translations = [];

        foreach ($pending as $locale => $fields) {
            $this->writeTranslation($locale, $fields);
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getPendingTranslations(): array
    {
        return $this->pending_translations;
    }

    public array $fields_ignored_for_translation = ['id', 'translatable_parent_id', 'lang'];

    public function getFieldsIgnoredForTranslation(): array
    {
        $values = array_values(array_unique(array_merge(
            $this->fields_ignored_for_translation,
            config('translatable.fields_ignored_for_translation'),
        )));

        return $values;
    }

    public function translations()
    {
        if ($this->isRootTranslation()) {
            return $this->childTranslations();
        }

        return static::query()->where(function ($query) {
            return $query->where($this->getKeyName(), $this->translatable_parent_id)
                ->orWhere('translatable_parent_id', $this->translatable_parent_id);
        })->where($this->getKeyName(), '!=', $this->getKey());
    }

    public function childTranslations()
    {
        return $this->hasMany(self::class, 'translatable_parent_id', 'id');
    }

    public function defaultTranslation()
    {
        return $this->belongsTo(self::class, 'translatable_parent_id', 'id');
    }

    public function isDefaultTranslation(): bool
    {
        return $this->getAttributeValue('translatable_parent_id') === null;
    }

    public function getTranslation(?string $locale = null): ?static
    {
        if ($locale === null) {
            $locale = translation_locale();
        }

        $defaultTranslation = $this->isDefaultTranslation() ? $this : $this->defaultTranslation;

        // Return fast if there is no default translation
        if (! $defaultTranslation) {
            return null;
        }

        // If the requested locale is the same as the default translation's lang, return the default translation
        if ($defaultTranslation->lang === $locale) {
            return $defaultTranslation;
        }

        // Attempt to fetch the first translation within translatable rows
        return $defaultTranslation->translations()->where('lang', $locale)->first();
    }

    public function translate(string $field, ?string $locale = null, bool $fallback = true): mixed
    {
        // Use current app locale whenever locale isn't provided
        if ($locale === null) {
            $locale = translation_locale();
        }

        // If the locale is not allowed then return null
        if (! $this->isAllowedTranslationLocale($locale)) {
            return $fallback ? $this->getAttributeValue($field) : null;
        }

        // If the field is not in the translatable fields list then return null
        if (! $this->isTranslatable($field)) {
            return $fallback ? $this->getAttributeValue($field) : null;
        }

        if (array_key_exists($field, $this->pending_translations[$locale] ?? [])) {
            return $this->pending_translations[$locale][$field];
        }

        $translation = $this->getTranslation($locale);

        if ($translation) {
            return $translation->getAttributeValue($field);
        }

        return $fallback ? $this->getAttributeValue($field) : null;
    }

    public function getDefaultTranslationLocale(): string
    {
        // yes this may not be the correct default translation locale
        // but we do this check to see if we need to fetch another row
        // from the database.
        return $this->translatable_parent_id
            ? $this->defaultTranslation->lang
            : $this->getAttributeValue('lang');
    }

    public function clearTranslations(?string $locale = null): void
    {
        if (! $locale) {
            // nuke all except the main one
            $parent_id = $this->isDefaultTranslation() ? $this->id : $this->translatable_parent_id;
            self::query()->where('translatable_parent_id', $parent_id)->withTrashed()->forceDelete();
        } else {
            // check the current one lang, if it's correct delete it
            if ($this->lang === $locale) {
                $this->delete();
            } else {
                $defaultTranslation = $this->isDefaultTranslation() ? $this : $this->defaultTranslation;
                if ($defaultTranslation->lang === $locale) {
                    $defaultTranslation->delete();
                }
                $defaultTranslation->translations()->where('lang', $locale)->delete();
            }
        }
    }

    public function hasTranslation(?string $locale = null): bool
    {
        if ($locale === null) {
            $locale = translation_locale();
        }

        // if the current one is the correct lang no need to fetch from database
        if ($this->lang === $locale) {
            return true;
        }

        if (isset($this->pending_translations[$locale])) {
            return true;
        }

        if ($this->isDefaultTranslation()) {
            $translation = $this->translations()->where('lang', $locale);

            return $translation->exists();
        }
        $translation = self::query()->where([
            'translatable_parent_id' => $this->translatable_parent_id,
            'lang' => $locale,
        ])->orWhere([
            'id' => $this->translatable_parent_id,
            'lang' => $locale,
        ]);

        return $translation->exists();

    }

    /**
     * Add a translation and save it to its row
     *
     * @throws LanguageNotAllowedException
     * @throws FieldNotAllowedException
     */
    public function addTranslation(string $locale, string $field, mixed $value): static
    {
        $this->ensureTranslationAllowed($locale, $field);

        return $this->writeTranslation($locale, [$field => $value]);
    }

    /**
     * Stage a translation; the row is written when this model is saved
     *
     * @throws LanguageNotAllowedException
     * @throws FieldNotAllowedException
     */
    protected function stageTranslation(string $locale, string $field, mixed $value): static
    {
        $this->ensureTranslationAllowed($locale, $field);

        if ($locale === $this->primaryLocale()) {
            $this->setAttributeInternal($field, $value);

            return $this;
        }

        $this->pending_translations[$locale][$field] = $value;

        return $this;
    }

    /**
     * @throws LanguageNotAllowedException
     * @throws FieldNotAllowedException
     */
    protected function ensureTranslationAllowed(string $locale, string $field): void
    {
        if (! $this->isAllowedTranslationLocale($locale)) {
            throw LanguageNotAllowedException::create($locale);
        }

        if (! $this->isTranslatable($field)) {
            throw FieldNotAllowedException::create($field, $locale);
        }
    }

    /**
     * Write the given fields to the row for the locale, creating it from the default translation if needed
     *
     * @param  array<string, mixed>  $fields
     */
    protected function writeTranslation(string $locale, array $fields): static
    {
        // Get the default translation (root/parent translation). If the current record is
        // the default, we will use it, otherwise we will fetch the default translation
        $default_translation = $this->isDefaultTranslation() ? $this : $this->defaultTranslation;

        if ($default_translation->lang === $locale) {
            foreach ($fields as $field => $value) {
                $default_translation->setAttributeInternal($field, $value);
            }

            $default_translation->save();

            return $default_translation;
        }

        $translation = $default_translation->translations()->where('lang', $locale)->first();

        // if there is none, make a new translation of the default translation,
        // copying its columns so that none are left empty. Its own key and
        // timestamps are left for Eloquent to set.
        if (! $translation) {
            $translation = new self;

            $skipped = [$this->getKeyName()];

            if ($this->usesTimestamps()) {
                $skipped[] = $this->getCreatedAtColumn();
                $skipped[] = $this->getUpdatedAtColumn();
            }

            foreach ($this->getAllAttributes() as $attribute) {
                if (in_array($attribute, $skipped, true)) {
                    continue;
                }

                $translation->setAttributeInternal($attribute, $default_translation->getAttributeValue($attribute));
            }

            $translation->setAttributeInternal('translatable_parent_id', $default_translation->getKey());
            $translation->setAttributeInternal('lang', $locale);
        }

        foreach ($fields as $field => $value) {
            $translation->setAttributeInternal($field, $value);
        }

        $translation->save();

        return $translation;
    }

    /**
     * @throws CannotDeletePrimaryTranslationException
     */
    public function deleteTranslation(string $locale): void
    {
        $defaultTranslation = $this->isDefaultTranslation() ? $this : $this->defaultTranslation;
        $translation = $defaultTranslation->translations()->where('lang', $locale)->first();
        if (empty($translation->translatable_parent_id)) {
            throw CannotDeletePrimaryTranslationException::create($locale);
        }
        $translation->delete();

    }

    public function deleteTranslations(): void
    {
        if (empty($this->translatable_parent_id)) {
            $this->translations()->delete();
        } else {
            $this->defaultTranslation->translations()->delete();
        }
    }

    /**
     * Check if the current translation is the root translation.
     */
    public function isRootTranslation(): bool
    {
        return empty($this->translatable_parent_id);
    }

    public function scopeRootTranslations(Builder $query): Builder
    {
        return $query->whereNull('translatable_parent_id');
    }

    /**
     * Check if the current translation can update its parent translation.
     */
    public function canUpdateTranslatableParent(): bool
    {
        return (! $this->isRootTranslation()) || (! $this->hasTranslation());
    }
}
