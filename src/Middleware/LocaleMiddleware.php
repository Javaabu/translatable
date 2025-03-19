<?php

namespace Javaabu\Translatable\Middleware;

use Closure;
use Illuminate\Http\Request;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Facades\Translatable as TranslatableFacade;

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
     * @return string|null
     */
    protected function getUserLocale(Request $request)
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
     * @return \Javaabu\Translatable\Models\Language|null
     */
    protected function getLocaleFromRequest(Request $request): ?\Javaabu\Translatable\Models\Language
    {
        $language = null;

        if ($route_language = $request->route('language')) {
            // Check language from route
            $language = $route_language;
        } elseif ($input_language = $request->input('language')) {
            // Check language from input
            $language = $input_language;
        } elseif ($code = $request->query('lang')) {
            // Check language from query param
            $language = TranslatableFacade::isAllowedTranslationLocale($code) ? $code : null;
        }

        if (! $language) {
            return null;
        }

        if (!($language instanceof \Javaabu\Translatable\Models\Language)) {
            return Languages::get($language);
        }

        return $language;
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
        if ($locale instanceof \Javaabu\Translatable\Models\Language) {
            $locale = $locale->code;
        }

        if (!is_api_request($request)) {
            $request->session()->put('language', $locale);
        }

        app()->setLocale($locale);
    }
}
