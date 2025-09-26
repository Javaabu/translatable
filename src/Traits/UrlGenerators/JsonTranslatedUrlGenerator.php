<?php

namespace Javaabu\Translatable\Traits\UrlGenerators;

use Javaabu\Translatable\Models\Language;
use Request;

trait JsonTranslatedUrlGenerator
{
    use BaseUrlGenerator;

    public function url(string $action = 'show', Language|string|null $locale = null, ?string $portal = null): string
    {
        if ( ! $portal) {
            $portal = Request::portal();
        }

        $model_route_name = $this->getModelRouteName($portal);

        $route_name = $portal ? "{$portal}.{$model_route_name}.{$action}" : "{$model_route_name}.{$action}";

        $params = $this->getAdditionalRouteParams($action, $locale, $portal);

        if ( ! in_array($action, ['index', 'store', 'create', 'trash'])) {
            $params[] = $this->routeKeyForPortal($portal);
        }

        return translate_route(name: $route_name, parameters: $params, locale: $locale);
    }
}
