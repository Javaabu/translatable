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

    /**
     * Get a single language
     *
     * @param  string|null  $code
     * @return Language
     */
    public function get(string $code = null): Language
    {
        if (! $code) {
            $code = $this->currentLanguageCode();
        }

        return app(LanguageRegistrar::class)->getLanguages(['code' => $code])->first();
    }

    /**
     * Get current language code
     *
     * @return string
     */
    public function currentLanguageCode() : string
    {
        return app()->getLocale();
    }

    /**
     * Set the app locale to the default translation locale
     *
     * @return void
     */
    public function setToTranslationLocale() : void
    {
        app()->setLocale($this->defaultLanguageCode());
    }

    /**
     * Get default language code
     *
     * @return string
     */
    public function defaultLanguageCode() : string
    {
        return Language::getDefaultTranslationLocale();
    }

    /**
     * Set the app locale to the app locale
     *
     * @return void
     */
    public function setToAppLocale() : void
    {
        app()->setLocale($this->defaultAppLocale());
    }

    /**
     * Get default app locale
     *
     * @return string
     */
    public function defaultAppLocale() : string
    {
        return Language::getDefaultAppLocale();
    }

    /**
     * Check if the language is the current language
     *
     * @param  string|Language  $code
     * @return bool
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
     *
     * @param  string|Language|null  $code
     * @return bool
     */
    public function isDefault(string|Language $code = null): bool
    {
        if ($code instanceof Language) {
            $code = $code->code;
        }

        if (! $code) {
            $code = $this->currentLanguageCode();
        }

        return $code === $this->defaultLanguageCode();
    }
}
