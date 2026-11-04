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
     */
    public function handle(Request $request, Closure $next, bool $set_app_locale = true): mixed
    {
        $locale = $this->getUserLocale($request);

        if (empty($locale)) {
            $locale = $this->getDefaultLocale();
        }

        $this->setUserLocale($locale, $request, $set_app_locale);

        return $next($request);
    }

    /**
     * Get the user locale
     */
    protected function getUserLocale(Request $request): Language|string|null
    {
        // first try the request
        $locale = $this->getLocaleFromRequest($request);

        if ($locale) {
            return $locale;
        }

        if ($this->usesSession($request)) {
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
     */
    protected function getLocaleFromRequest(Request $request): ?Language
    {
        $locale = $request->route('language')
            ?: $request->input('language')
                ?: $request->query('lang');

        if (! $locale || ! TranslatableFacade::isAllowedTranslationLocale($locale)) {
            return null;
        }

        return $locale instanceof Language ? $locale : Languages::get($locale);
    }

    /**
     * Get the locale from the session
     */
    protected function getLocaleFromSession(Request $request): ?string
    {
        return $request->session()->get('language');
    }

    /**
     * Get the default locale
     */
    protected function getDefaultLocale(): string
    {
        return config('translatable.default_locale');
    }

    /**
     * Whether the locale is kept in the session: not for API requests, or requests without one
     *
     * Uses javaabu/helpers' is_api_request() when the app has it, else the `app.api_prefix` (api) path.
     */
    protected function usesSession(Request $request): bool
    {
        if (! $request->hasSession()) {
            return false;
        }

        if (function_exists('is_api_request')) {
            return ! is_api_request($request);
        }

        $prefix = trim((string) config('app.api_prefix', 'api'), '/');

        return ! $request->is($prefix . '/*');
    }

    /**
     * Set the user locale
     */
    protected function setUserLocale($locale, Request $request, bool $set_app_locale = true): void
    {
        // Convert to language code if it is an object
        if ($locale instanceof Language) {
            $locale = $locale->code;
        }

        if ($this->usesSession($request)) {
            $request->session()->put('language', $locale);
        }

        Languages::setCurrentLocale($locale, $set_app_locale);
    }
}
