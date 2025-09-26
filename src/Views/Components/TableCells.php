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
    ) {
        $this->languages = Languages::allExceptCurrent();
    }

    public function getUrl(string $action, string $locale): string
    {
        $url = $this->model->url($action, $locale);

        // If the model is we need to add "lang_parent" to the params
        if ($this->isModelDbTranslatable() && ($action === 'create')) {
            $url = add_query_arg('lang_parent', $this->model->id, $url);
        }

        return $url;
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
