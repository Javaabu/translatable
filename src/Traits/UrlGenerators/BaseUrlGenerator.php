<?php

namespace Javaabu\Translatable\Traits\UrlGenerators;

trait BaseUrlGenerator
{
    /**
     * Standardized model route name.
     * Can customize this on each model as well.
     *
     * @param  string|null  $portal
     * @return string
     */
    public function getModelRouteName(?string $portal = null): string
    {
        return str(class_basename($this))->plural()->snake('-')->toString();
    }

    /**
     * Override in model for nested params.
     * Useful for nested resources.
     *
     * @param  string       $action
     * @param  string|null  $locale
     * @param  string|null  $portal
     * @return array
     */
    public function getAdditionalRouteParams(string $action, ?string $locale, ?string $portal): array
    {
        return [];
    }

    /**
     * Get the route key for the current specific model instance.
     * $portal parameter to be used to customize the key based on portal if needed.
     * Default is just the ID.
     * Can be overridden in the model if needed.
     *
     * @param  string|null  $portal
     * @return string
     */
    public function routeKeyForPortal(?string $portal): string
    {
        return (string) $this->id;
    }
}
