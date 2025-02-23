<?php

namespace Javaabu\Translatable\Exceptions;

use Exception;

class LanguageNotAllowedException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function create(string $locale): LanguageNotAllowedException
    {
        return new self($locale . ' language not allowed');
    }
}
