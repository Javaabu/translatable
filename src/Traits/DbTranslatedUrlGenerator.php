<?php

namespace Javaabu\Translatable\Traits;

use Illuminate\Support\Str;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Models\Language;
use Request;

trait DbTranslatedUrlGenerator
{
    public function url(string $action = 'show', Language|string|null $locale = null, ?string $portal = null): string
    {
        if (!$portal) {
            $portal = Request::portal();
        }

        $localeCode = $locale instanceof Language ? $locale->code : $locale;

        // Figure out route name based on namespace and action
        $model_route_name = Str::of(class_basename($this))->plural()->snake('-')->toString();

        $route_name = $portal ? "{$portal}.{$model_route_name}.{$action}" : "{$model_route_name}.{$action}";

        $params = [];
        $skipId = ['index', 'store', 'create', 'trash'];

        // No ID needed for these actions
        if (in_array($action, $skipId, true)) {
            return translate_route(name: $route_name, parameters: [], locale: $localeCode);
        }

        // Decide which ID (if any) to append, or switch to create route
        if ($localeCode === null || Languages::isCurrent($localeCode)) {
            $params[] = $this->routeKeyForPortal($portal);
        } elseif ($translation = $this->getTranslation($localeCode)) {
            $params[] = $translation->routeKeyForPortal($portal);
        } else {
            $route_name = $portal ? "{$portal}.{$model_route_name}.create" : "{$model_route_name}.create";
        }

        return translate_route(name: $route_name, parameters: $params, locale: $localeCode);
    }

    public function routeKeyForPortal(?string $portal): string
    {
        return (string) $this->id;
    }
}
