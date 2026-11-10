<?php

namespace Javaabu\Translatable\Views\Components;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Javaabu\Translatable\LocaleInput;
use Javaabu\Translatable\Translatable;

/**
 * One javaabu/forms input per locale for a translatable field, in tabs or stacked
 *
 * <code><x-translatable::field type="textarea" name="body" :label="__('Body')" required /></code>
 */
class Field extends Component
{
    protected string $view = 'field';

    /** @var Collection<string, LocaleInput> */
    public Collection $inputs;

    public string $active;

    public mixed $model;

    public function __construct(
        public string $name,
        public string $type = 'text',
        public string $label = '',
        mixed $model = null,
        public bool $required = false,
        public bool $requireAll = false,
        public string $layout = 'tabs',
        string $framework = '',
    ) {
        parent::__construct($framework);

        $this->model = $this->resolveModel($model);

        $this->inputs = $this->shouldTranslate()
            ? Translatable::inputs($name, $this->model)
            : collect();

        $this->active = $this->firstLocaleWithErrors() ?? $this->inputs->keys()->first() ?? '';
    }

    /**
     * Translatable when the bound model supports the field, or, with no model (a create form),
     * whenever translations are on
     */
    protected function shouldTranslate(): bool
    {
        if (is_object($this->model)) {
            return Translatable::supports($this->model, $this->name);
        }

        return Translatable::enabled();
    }

    public function activeTab(): string
    {
        return $this->stringToId($this->name) . '-' . $this->active;
    }

    public function isTranslatable(): bool
    {
        return $this->inputs->isNotEmpty();
    }

    public function inputComponent(): string
    {
        return Str::startsWith($this->type, 'forms::') ? $this->type : "forms::{$this->type}";
    }

    public function inputLabel(LocaleInput $input): string
    {
        return $input->label($this->label ?: null);
    }

    public function isRequired(LocaleInput $input): bool
    {
        return $this->required && ($input->is_primary || $this->requireAll);
    }

    /**
     * The tab and panel name for a locale: title-dv
     */
    public function tabName(LocaleInput $input): string
    {
        return $this->stringToId($this->name) . '-' . $input->locale->code;
    }

    /**
     * The slot variable javaabu/forms' tabs read for a tab name
     */
    public function slotName(LocaleInput $input): string
    {
        return Str::of($this->tabName($input))->camel()->snake()->replace('_', ' ')->camel()->toString();
    }

    /**
     * @return array<int, array{name: string, title: string}>
     */
    public function tabs(): array
    {
        return $this->inputs
            ->map(fn (LocaleInput $input) => [
                'name' => $this->tabName($input),
                'title' => $input->locale->native_name,
            ])
            ->values()
            ->all();
    }

    /**
     * HTML attributes for a locale's input: direction and language, and the Thaana keyboard for Dhivehi
     *
     * @return array<string, mixed>
     */
    public function inputAttributes(LocaleInput $input): array
    {
        $attributes = ['dir' => $input->locale->direction, 'lang' => $input->locale->locale];

        // What javaabu/forms' `thaana` option adds; its script turns on the Thaana keyboard
        if ($input->locale->isThaana()) {
            $attributes['data-thaana'] = '';
        }

        return $attributes;
    }

    protected function firstLocaleWithErrors(): ?string
    {
        $errors = session('errors');

        if (! $errors instanceof ViewErrorBag) {
            return null;
        }

        return $this->inputs
            ->first(fn (LocaleInput $input) => $errors->has($input->name))
            ?->locale
            ->code;
    }
}
