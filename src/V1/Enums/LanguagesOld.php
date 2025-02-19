<?php


namespace Javaabu\Translatable\Old\Enums;

use Illuminate\Support\Facades\Route;
use Javaabu\Translatable\Enums\Flags;
use Javaabu\Translatable\Translatable;
use function Javaabu\Translatable\Enums\public_url;

abstract class LanguagesOld
{
    public static function getDefaultAppLocale()
    {
        return config('app.fallback_locale');
    }

    /**
     * Initialize labels
     */
    protected static function initLabels()
    {
        static::$labels = [
            static::DV => __('Dhivehi'),
            static::EN => __('English'),
        ];
    }

    protected static function getFlag($key): string
    {
        return static::$flags[$key] ?? '';
    }

    public static function flagUrl($key): string
    {
        return Flags::getFlagUrl(self::getFlag($key));
    }

    public static function getLocaleFlag($current_locale = null, $opposite = false): string
    {
        if (!$current_locale) {
            $current_locale = app()->getLocale();
        }

        $locale = $opposite ? self::getOppositeLocale() : $current_locale;

        return self::flagUrl($locale);
    }

    public static function getOppositeLocaleFlag($current_locale = null): string
    {
        return self::getLocaleFlag($current_locale, true);
    }

    public static function getDefaultTranslationLocale(): string
    {
        return config('translations.default_translation_locale');
    }

    public static function isRtl($value): bool
    {
        return $value == self::DV;
    }

    public static function getDirection($current_locale = null): string
    {
        if (!$current_locale) {
            $current_locale = app()->getLocale();
        }

        return self::isRtl($current_locale) ? 'rtl' : 'ltr';
    }

    public static function getOppositeLocale($currentLocale = null): string
    {
        if (!$currentLocale) {
            $currentLocale = app()->getLocale();
        }

        return $currentLocale == self::DV ? self::EN : self::DV;
    }

    public static function translateCurrentRoute(): string
    {
        $current_route = Route::getCurrentRoute()->getName();

        $route_params = Route::getCurrentRoute()->parameters();

        $switch_to = self::getOppositeLocale();

        $route_params['language'] = $switch_to;

        return route($current_route, $route_params);
    }

    public static function getLocalizedUrl($translatable, $locale = null): string
    {
        if (!$locale) {
            $locale = self::getOppositeLocale();
        }

        $url = null;

        if ($translatable instanceof Translatable) {
            $url = $translatable->getLocalizedUrl($locale);
        } elseif ($translatable) {
            $url = public_url('/' . $locale . '/' . ltrim($translatable, '/'));
        }

        return $url ?: public_url('/' . $locale);
    }

    /**
     * Set current session locale
     *
     * @return string
     */
    public static function getSessionLocale(): string
    {
        return session()->get('language', static::getDefaultTranslationLocale());
    }
}
