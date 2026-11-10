<?php

namespace Javaabu\Translatable\Views\Components;

use Illuminate\Support\Collection;
use Javaabu\Translatable\Locale;
use Javaabu\Translatable\Translatable;

/**
 * One table heading per language (except the current one), for <code><x-translatable::table-cells /></code>
 */
class Titles extends Component
{
    protected string $view = 'table-titles';

    /** @var Collection<int, Locale> */
    public Collection $languages;

    public function __construct(string $framework = '')
    {
        parent::__construct($framework);

        $current = Translatable::currentLocale();

        $this->languages = Translatable::locales()->reject(fn (Locale $locale) => $locale->code === $current)->values();
    }
}
