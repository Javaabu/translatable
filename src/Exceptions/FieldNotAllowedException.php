<?php

namespace Javaabu\Translatable\Exceptions;

use Exception;

class FieldNotAllowedException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function create(string $field, string $locale): FieldNotAllowedException
    {
        return new self($field . ' field not allowed for locale ' . $locale);
    }
}
