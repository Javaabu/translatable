<?php

namespace Javaabu\Translatable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Javaabu\Translatable\Languages
 */
class Languages extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'languages';
    }
}
