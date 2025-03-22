<?php

namespace Javaabu\Translatable\Views\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Javaabu\Translatable\Facades\Languages;

class Titles extends Component
{
    public Collection $languages;

    public function __construct()
    {
        $this->languages = Languages::allExceptCurrent();
    }

    public function render(): View
    {
        return view('translatable::table-titles');
    }
}
