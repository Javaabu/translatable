<?php

namespace Javaabu\Translatable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Javaabu\Translatable\Translatable
 *
 */
class Translatable extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'translatable';
    }
}
