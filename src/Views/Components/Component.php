<?php

namespace Javaabu\Translatable\Views\Components;

use Javaabu\Forms\FormsDataBinder;
use Javaabu\Forms\Views\Components\Component as FormsComponent;

/**
 * Base for the Blade components. They're built from javaabu/forms v2 components,
 * so they follow the app's forms framework (Onu or Bootstrap 6).
 *
 * Views resolve as translatable::{framework}.{view}, falling back to translatable::common.{view}.
 */
abstract class Component extends FormsComponent
{
    public function getView(): string
    {
        return 'translatable::{framework}.' . $this->view;
    }

    /**
     * Layout utilities for the few wrappers javaabu/forms has no component for
     */
    public function layoutClasses(string $key): string
    {
        $bootstrap = str_starts_with($this->framework, 'bootstrap');

        return match ($key) {
            'inline-list' => $bootstrap ? 'd-flex flex-wrap align-items-center gap-1' : 'flex flex-wrap items-center gap-1',
            'center' => $bootstrap ? 'd-flex justify-content-center align-items-center gap-1' : 'flex items-center justify-center gap-1',
            default => '',
        };
    }

    /**
     * The model given to the component, or the one bound by the surrounding <x-forms::form :model>
     */
    protected function resolveModel(mixed $model): mixed
    {
        if ($model) {
            return $model;
        }

        return app(FormsDataBinder::class)->get();
    }
}
