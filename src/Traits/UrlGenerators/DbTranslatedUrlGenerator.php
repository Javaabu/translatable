<?php

namespace Javaabu\Translatable\Traits\UrlGenerators;

use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Models\Language;
use Request;

trait DbTranslatedUrlGenerator
{
    use BaseUrlGenerator;

    public function url(string $action = 'show', Language|string|null $locale = null, ?string $portal = null): string
    {
        if (!$portal) {
            $portal = Request::portal();
        }

        $localeCode = $locale instanceof Language ? $locale->code : $locale;

        $model_route_name = $this->getModelRouteName($portal);

        $route_name = $portal ? "{$portal}.{$model_route_name}.{$action}" : "{$model_route_name}.{$action}";

        $params = $this->getAdditionalRouteParams($action, $locale, $portal);
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
}
