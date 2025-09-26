<?php

namespace Javaabu\Translatable\Traits;

use Javaabu\Translatable\Models\Language;
use Request;

trait JsonTranslatedUrlGenerator
{
    public function url(string $action = 'show', Language|string|null $locale = null, ?string $portal = null): string
    {
        if ( ! $portal) {
            $portal = Request::portal();
        }

        // Figure out route name based on namespace and action
        $model_route_name = str(class_basename($this))->plural()->snake('-')->toString();

        $route_name = $portal ? "{$portal}.{$model_route_name}.{$action}" : "{$model_route_name}.{$action}";

        $params = [];

        if ( ! in_array($action, ['index', 'store', 'create', 'trash'])) {
            $params[] = $this->routeKeyForPortal($portal);
        }

        return translate_route(name: $route_name, parameters: $params, locale: $locale);
    }

    public function routeKeyForPortal(?string $portal): string
    {
        return (string) $this->id;
    }
}
