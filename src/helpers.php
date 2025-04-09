<?php

use Illuminate\Support\Arr;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Models\Language;

if (!function_exists('appGetLocale')) {
    /**
     * One function helper to get the current locale.
     *
     * @return string|array|null
     */
    function appGetLocale(): array|string|null
    {
        return app()->getLocale();
    }
}

if (!function_exists('_d')) {
    /**
     * Translate the given message and use the default locale if locale not specified.
     *
     * @param  string|null  $key
     * @param  array        $replace
     * @param  string|null  $locale
     * @return string|array|null
     */
    function _d(string $key = null, array $replace = [], string $locale = null): array|string|null
    {
        $locale = $locale ?: app()->getFallbackLocale();

        return __($key, $replace, $locale);
    }
}

if (!function_exists('translate_route')) {
    /**
     * Generate a translatable route for the model.
     *
     * @param  string                $name
     * @param  array|string|mixed    $parameters
     * @param  bool                  $absolute
     * @param  string|Language|null  $locale
     * @return string
     */
    function translate_route(string $name, mixed $parameters = [], bool $absolute = true, Language|string $locale = null): string
    {
        if ($locale instanceof Language) {
            $locale = $locale->code;
        }

        if (!$locale) {
            $locale = app()->getLocale();
        }

        $parameters = Arr::wrap($parameters);
        $parameters['language'] = $locale;

        return route($name, $parameters, $absolute);
    }
}

if (!function_exists('locale_direction')) {
    /**
     * Generate a translatable route for the model.
     *
     * @param  Language|string|null  $language
     * @return string
     */
    function locale_direction(Language|string $language = null): string
    {
        $language = $language instanceof Language
            ? $language
            : Languages::get($language ?? app()->getLocale());

        return $language && $language->is_rtl ? 'rtl' : 'ltr';
    }
}
