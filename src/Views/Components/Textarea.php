<?php

namespace Javaabu\Translatable\Views\Components;

class Textarea extends Field
{
    public function __construct(
        string $name,
        string $label = '',
        mixed $model = null,
        bool $required = false,
        bool $requireAll = false,
        string $layout = 'tabs',
        string $framework = '',
    ) {
        parent::__construct($name, 'textarea', $label, $model, $required, $requireAll, $layout, $framework);
    }
}
