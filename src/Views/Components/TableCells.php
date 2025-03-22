<?php

namespace Javaabu\Translatable\Views\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Javaabu\Translatable\Contracts\Translatable;
use Javaabu\Translatable\Facades\Languages;

class TableCells extends Component
{
    public Collection $languages;

    public function __construct(
        public Translatable $model,
        public string       $create_url = ''
    )
    {
        $this->languages = Languages::allExceptCurrent();
    }

    public function render(): View
    {
        return view('translatable::table-cells');
    }
}
