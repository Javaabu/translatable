<?php

use Illuminate\Support\Arr;
use Javaabu\Translatable\Models\Language;

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
