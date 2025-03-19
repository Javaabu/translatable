<?php

namespace Javaabu\Translatable;

use \Javaabu\Translatable\Facades\Languages as LanguagesFacade;

class Translatable
{
    /**
     * Return all the translation locales allowed in the config file
     *
     * @return array
     */
    public function getAllowedTranslationLocales(): array
    {
//        return array_keys(config('translatable.allowed_translation_locales'));
        return LanguagesFacade::all()->map(function ($lang) {
            return $lang->code;
        })->toArray();
    }

    /**
     * Check if a given locale is allowed to translate to
     *
     * @param  string  $locale
     * @return bool
     */
    public function isAllowedTranslationLocale(string $locale): bool
    {
        return in_array($locale, $this->getAllowedTranslationLocales());
    }
}
