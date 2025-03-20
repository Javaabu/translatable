<?php

namespace Javaabu\Translatable\Middleware;

use Closure;
use Illuminate\Http\Request;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Facades\Translatable as TranslatableFacade;
use Javaabu\Translatable\Models\Language;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $locale = $this->getUserLocale($request);

        if (empty($locale)) {
            $locale = $this->getDefaultLocale();
        }


        $this->setUserLocale($locale, $request);

        return $next($request);
    }

    /**
     * Get the user locale
     *
     * @param  Request  $request
     * @return Language|string|null
     */
    protected function getUserLocale(Request $request): Language|string|null
    {
        // first try the request
        $locale = $this->getLocaleFromRequest($request);

        if ($locale) {
            return $locale;
        }

        if (!is_api_request($request)) {
            // then try the session
            $locale = $this->getLocaleFromSession($request);
            if ($locale) {
                return $locale;
            }
        }

        return null;
    }

    /**
     * Get the locale from the route
     *
     * @param  Request  $request
     * @return Language|null
     */
    protected function getLocaleFromRequest(Request $request): ?Language
    {
        $locale = $request->route('language')
            ?: $request->input('language')
                ?: $request->query('lang');

        if (!$locale || !TranslatableFacade::isAllowedTranslationLocale($locale)) {
            return null;
        }

        return $locale instanceof Language ? $locale : Languages::get($locale);
    }

    /**
     * Get the locale from the session
     *
     * @param  Request  $request
     * @return string|null
     */
    protected function getLocaleFromSession(Request $request): ?string
    {
        return $request->session()->get('language');
    }

    /**
     * Get the default locale
     *
     * @return string
     */
    protected function getDefaultLocale(): string
    {
        return config('translatable.default_locale');
    }

    /**
     * Set the user locale
     *
     * @param           $locale
     * @param  Request  $request
     */
    protected function setUserLocale($locale, Request $request): void
    {
        // Convert to language code if it is an object
        if ($locale instanceof Language) {
            $locale = $locale->code;
        }

        if (!is_api_request($request)) {
            $request->session()->put('language', $locale);
        }

        app()->setLocale($locale);
    }
}
