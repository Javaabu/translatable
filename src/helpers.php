<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Javaabu\Translatable\Contracts\DbTranslatable;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\ModelAttribute;
use Javaabu\Translatable\Models\Language;

if ( ! function_exists('translation_locale')) {
    /**
     * One function helper to get the current locale.
     */
    function translation_locale(): ?string
    {
        return Languages::currentLanguageCode();
    }
}

if ( ! function_exists('appGetLocale')) {
    /**
     * One function helper to get the current locale.
     */
    function appGetLocale(): array|string|null
    {
        return app()->getLocale();
    }
}

if ( ! function_exists('_d')) {
    /**
     * Translate the given message and use the default locale if locale not specified.
     */
    function _d(?string $key = null, array $replace = [], ?string $locale = null): array|string|null
    {
        $locale = $locale ?: app()->getFallbackLocale();

        return __($key, $replace, $locale);
    }
}

if ( ! function_exists('translate_url')) {
    /**
     * Generate a translatable url for the application.
     *
     * @param  null  $locale
     */
    function translate_url(?string $path = null, array $parameters = [], ?bool $secure = null, $locale = null): string
    {
        if ( ! $locale) {
            $locale = translation_locale();
        }

        $path = $locale . '/' . ltrim($path, '/');

        return url($path, $parameters, $secure);
    }
}

if ( ! function_exists('translate_route')) {
    /**
     * Generate a translatable url using the route method.
     *
     * @param  array|string|mixed  $parameters
     */
    function translate_route(string $name, mixed $parameters = [], bool $absolute = true, Language|string|null $locale = null): string
    {
        if ($locale instanceof Language) {
            $locale = $locale->code;
        }

        if ( ! $locale) {
            $locale = translation_locale();
        }

        $parameters = Arr::wrap($parameters);
        $parameters['language'] = $locale;

        return route($name, $parameters, $absolute);
    }
}

if ( ! function_exists('translate_action')) {
    /**
     * Generate a translatable url using the action method.
     *
     * @param  array|string|mixed  $parameters
     */
    function translate_action(string|array $action, mixed $parameters = [], bool $absolute = true, Language|string|null $locale = null): string
    {
        if ($locale instanceof Language) {
            $locale = $locale->code;
        }

        if ( ! $locale) {
            $locale = translation_locale();
        }

        $parameters = Arr::wrap($parameters);
        $parameters['language'] = $locale;

        return Illuminate\Support\Facades\URL::action($action, $parameters, $absolute);
    }
}

if ( ! function_exists('locale_direction')) {
    /**
     * Generate a translatable route for the model.
     */
    function locale_direction(Language|string|null $language = null): string
    {
        $language = $language instanceof Language
            ? $language
            : Languages::get($language ?? translation_locale());

        return $language && $language->is_rtl ? 'rtl' : 'ltr';
    }
}

if ( ! function_exists('ma')) {
    /**
     * Get a model attribute instance
     */
    function ma(Model $model, string $attribute): ModelAttribute
    {
        return new ModelAttribute($model, $attribute);
    }
}

if ( ! function_exists('translate_old')) {
    /**
     * Retrieve an old input item or parent item value.
     */
    function translate_old(?string $key = null, ?DbTranslatable $lang_parent = null, mixed $default = null): mixed
    {
        return $lang_parent ? ($lang_parent->{$key} ?: $default) : old($key, $default);
    }
}
