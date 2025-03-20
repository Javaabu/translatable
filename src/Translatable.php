<?php

namespace Javaabu\Translatable;

use \Javaabu\Translatable\Facades\Languages as LanguagesFacade;
use Javaabu\Translatable\Models\Language;

class Translatable
{
    /**
     * Return all the translation locales allowed in the config file
     *
     * @return array
     */
    public function getAllowedTranslationLocales(): array
    {
        return LanguagesFacade::all()->map(function ($lang) {
            return $lang->code;
        })->toArray();
    }

    /**
     * Check if a given locale is allowed to translate to
     *
     * @param  Language|string  $locale
     * @return bool
     */
    public function isAllowedTranslationLocale(Language|string $locale): bool
    {
        if ($locale instanceof Language) {
            $locale = $locale->code;
        }

        return in_array($locale, $this->getAllowedTranslationLocales());
    }
}
