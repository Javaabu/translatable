<?php

namespace Javaabu\Translatable;

use Illuminate\Support\Collection;
use Javaabu\Translatable\Models\Language;

class Languages
{
    public function all(): Collection
    {
        return app(LanguageRegistrar::class)->getLanguages();
    }

    public function allExceptCurrent(): Collection
    {
        return app(LanguageRegistrar::class)->getLanguages()->whereNotIn('locale', [app()->getLocale()]);
    }

    public function except($code): Collection
    {
        if ( ! is_array($code)) {
            $code = [$code];
        }

        if ( ! $code) {
            return $this->all();
        }

        return $this->all()->filter(function ($language) use ($code) {
            return ! in_array($language->code, $code);
        });
    }

    /**
     * Get a single language
     */
    public function get(?string $code = null): ?Language
    {
        if ( ! $code) {
            $code = $this->currentLanguageCode();
        }

        return app(LanguageRegistrar::class)->getLanguages(['code' => $code])->first() ?? null;
    }

    /**
     * Get direction for a language
     */
    public function getDirection(?string $code = null): string
    {
        return $this->get($code)?->is_rtl ? 'rtl' : 'ltr';
    }

    public function isRtl(?string $code = null): bool
    {
        return $this->getDirection($code) === 'rtl';
    }

    /**
     * Check if a given language is a valid language
     */
    public function has(?string $code = null): bool
    {
        return $this->get($code) !== null;
    }

    public function default(): Language
    {
        return $this->get($this->defaultLanguageCode());
    }

    /**
     * Get current language code
     */
    public function currentLanguageCode(): string
    {
        return app()->getLocale();
    }

    /**
     * Set the app locale to the default translation locale
     */
    public function setToTranslationLocale(): void
    {
        app()->setLocale($this->defaultLanguageCode());
    }

    /**
     * Get default language code
     */
    public function defaultLanguageCode(): string
    {
        return Language::getDefaultTranslationLocale();
    }

    /**
     * Set the app locale to the app locale
     */
    public function setToAppLocale(): void
    {
        app()->setLocale($this->defaultAppLocale());
    }

    /**
     * Get default app locale
     */
    public function defaultAppLocale(): string
    {
        return Language::getDefaultAppLocale();
    }

    /**
     * Check if the language is the current language
     */
    public function isCurrent(string|Language $code): bool
    {
        $current = $this->currentLanguageCode();

        if ($code instanceof Language) {
            $code = $code->code;
        }

        return $code === $current;
    }

    /**
     * Check if the language is the default
     */
    public function isDefault(string|Language|null $code = null): bool
    {
        if ($code instanceof Language) {
            $code = $code->code;
        }

        if ( ! $code) {
            $code = $this->currentLanguageCode();
        }

        return $code === $this->defaultLanguageCode();
    }
}
