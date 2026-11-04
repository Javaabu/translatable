<?php

namespace Javaabu\Translatable\Facades;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Javaabu\Translatable\Locale;
use Javaabu\Translatable\LocaleInput;
use Javaabu\Translatable\Models\Language;

/**
 * @method static bool enabled()
 * @method static bool supports(object|string|null $model, ?string $field = null)
 * @method static bool isDbTranslatable(object|string|null $model)
 * @method static Collection<int, Locale> locales()
 * @method static Locale|null locale(?string $code = null)
 * @method static string defaultLocale()
 * @method static string currentLocale()
 * @method static string primaryLocale(object|string|null $model = null)
 * @method static string inputName(string $field, string $locale)
 * @method static Collection<string, LocaleInput> inputs(string $field, object|string|null $model = null)
 * @method static array rules(object|string|null $model, array $rules, bool $require_all = false)
 * @method static array values(Model|null $model, array|null $fields = null)
 * @method static array status(Model|null $model, Closure|null $url = null)
 * @method static array shared()
 * @method static array getAllowedTranslationLocales()
 * @method static bool isAllowedTranslationLocale(Language|string $locale)
 *
 * @see \Javaabu\Translatable\Translatable
 */
class Translatable extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'translatable';
    }
}
