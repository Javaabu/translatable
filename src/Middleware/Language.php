<?php

namespace Javaabu\Translatable\Middleware;

use Closure;
use Illuminate\Http\Request;
use Javaabu\Translatable\Translatable;

class Language
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
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
     * @param Request $request
     * @return string|null
     */
    protected function getUserLocale(Request $request): ?string
    {
        // first try the request
        $locale = $this->getLocaleFromRequest($request);

        if ($locale) {
            return $locale;
        }

        if (! is_api_request($request)) {
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
     * @param Request $request
     * @return string|null
     */
    protected function getLocaleFromRequest(Request $request): ?string
    {
        if ($language = $request->route('language')) {
            // Check language from route
            return $language;
        } elseif ($language = $request->input('language')) {
            // Check language from input
            return $language;
        } elseif ($code = $request->query('lang')) {
            // Check language from query param
            return Translatable::class->isAllowedTranslationLocale($code) ? $code : null;
        }

        return null;
    }

    /**
     * Get the locale from the session
     *
     * @param Request $request
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
        return Translatable::class->getDefaultTranslationLocale();
    }

    /**
     * Set the user locale
     *
     * @param           $locale
     * @param Request $request
     */
    protected function setUserLocale($locale, Request $request): void
    {
        if (! is_api_request($request)) {
            $request->session()->put('language', $locale);
        }

        app()->setLocale($locale);
    }
}
