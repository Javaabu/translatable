<?php

namespace Javaabu\Translatable\Views\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Javaabu\Translatable\Contracts\DbTranslatable;
use Javaabu\Translatable\Contracts\Translatable;
use Javaabu\Translatable\Facades\Languages;

class TableCells extends Component
{
    public Collection $languages;

    public function __construct(
        public Translatable $model,
        public string $route_name = '',
        public array $route_params = [],
        public string $create_url = '',
    ) {
        $this->languages = Languages::allExceptCurrent();
    }

    public function getUrl(string $action, string $locale): string
    {
        $params = filled($this->route_params) ? $this->route_params : $this->model->getRouteParams();

        // A big assumption here is that the last parameter in the route params is the model ID.
        // If the action is 'create', we don't need the last parameter (usually the ID)
        if ($action === 'create') {
            $params = array_slice($params, 0, -1); // Remove the last parameter if it exists
        }

        // If the model is we need to add "lang_parent" to the params
        if ($this->isModelDbTranslatable()) {
            $params['lang_parent'] = $this->model->id;
        }

        return translate_route(
            filled($this->route_name) ? $this->route_name : $this->model->getRouteName() . '.' . $action,
            $params,
            locale: $locale,
        );
    }

    public function isModelDbTranslatable(): bool
    {
        return $this->model instanceof DbTranslatable;
    }

    public function render(): View
    {
        return view('translatable::table-cells');
    }
}
