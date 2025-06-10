<?php

namespace Javaabu\Translatable;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Translatable\Contracts\Translatable;


class ModelAttribute
{
    public function __construct(public Model $model, public string $attribute)
    {
    }

    public function __toString(): string
    {
        return $this->model->{$this->attribute};
    }

    public function translate($locale = null, $fallback = true)
    {
        if ($this->model instanceof Translatable) {
            return $this->model->translate($this->attribute, $locale, $fallback);
        }

        return $this->__toString();
    }
}
