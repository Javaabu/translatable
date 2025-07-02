<?php

namespace Javaabu\Translatable\Contracts;

interface DbTranslatable extends Translatable
{
    public function translations();

    public function defaultTranslation();

    public function getTranslation(?string $locale = null): ?static;

    public function isDefaultTranslation(): bool;

}
