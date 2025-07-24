<?php

namespace Javaabu\Translatable\Exceptions;

use Exception;

class CannotDeletePrimaryTranslationException extends Exception
{
    public function __construct($message, $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function create(string $locale): CannotDeletePrimaryTranslationException
    {
        return new self("Cannot delete {$locale} as it is the primary language for this translation.");
    }
}
